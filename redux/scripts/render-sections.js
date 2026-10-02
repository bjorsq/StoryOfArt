/**
 * Renders The Story of Art to video in sections, split at the section
 * headings (plus the introduction before the first heading)
 *
 * Sections are rendered in parallel by scripts/capture.js, on one frame grid
 * so they join exactly. Each section is written to a .partial file and renamed
 * when it's finished, so the render can be stopped and restarted: finished
 * sections are skipped. When all sections are done, a concat list is written
 * which ffmpeg can use to join them without re-encoding:
 *
 *   ffmpeg -f concat -safe 0 -i output/sections/concat.txt -c copy output/story-of-art.mp4
 *
 * Usage:
 *   node scripts/render-sections.js --list
 *   node scripts/render-sections.js [--jobs 4] [--fps 30] [--width 1440] [--aspect 5:3] [--only 1,5-8]
 *
 * Other options (e.g. --crf) are passed to capture.js.
 */
const fs = require('fs');
const os = require('os');
const path = require('path');
const { spawn } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const timeline = require(path.join(ROOT, 'timeline.json'));

const opts = { jobs: String(Math.max(1, Math.floor(os.cpus().length / 3))), fps: '30', width: '1440', aspect: '4:3', out: 'output/sections' };
const argv = process.argv.slice(2);
for (let i = 0; i < argv.length; i++) {
    const key = argv[i].replace(/^--/, '');
    if (key === 'list') {
        opts.list = true;
    } else {
        opts[key] = argv[++i];
    }
}
const fps = parseFloat(opts.fps);
const outDir = path.resolve(ROOT, opts.out);

/* sections start at each heading - section headings are the boxes which exit upwards */
const slug = str => str.toLowerCase().replace(/&/g, 'and').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
const starts = [{ title: 'Introduction', start: 0 }];
timeline.boxes.forEach(box => {
    if (box.exit < 0) {
        starts.push({ title: box.text, start: box.start });
    }
});
const sections = starts.map((s, i) => {
    const end = i + 1 < starts.length ? starts[i + 1].start : timeline.duration;
    const n = String(i).padStart(2, '0');
    return {
        n: i,
        title: s.title,
        start: s.start,
        end,
        /* frame range on the piece's frame grid: [first, last) */
        frames: [Math.round(s.start / 1000 * fps), Math.round(end / 1000 * fps)],
        file: `${n}-${slug(s.title)}.mp4`,
    };
});

function formatTime(ms) {
    const s = Math.round(ms / 1000);
    return Math.floor(s / 3600) + ':' + String(Math.floor(s / 60) % 60).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
}

/* --only 1,5-8 */
function selected(n) {
    if (!opts.only) return true;
    return opts.only.split(',').some(part => {
        const [a, b] = part.split('-').map(Number);
        return b === undefined ? n === a : n >= a && n <= b;
    });
}

if (opts.list) {
    sections.forEach(s => console.log(`${String(s.n).padStart(2)}  ${formatTime(s.start)}  ${formatTime(s.end - s.start).padStart(8)}  ${s.title}`));
    process.exit(0);
}

fs.mkdirSync(outDir, { recursive: true });
const passthrough = Object.entries(opts)
    .filter(([key]) => !['jobs', 'fps', 'width', 'aspect', 'out', 'only'].includes(key))
    .flatMap(([key, value]) => ['--' + key, value]);
const todo = sections.filter(s => selected(s.n) && !fs.existsSync(path.join(outDir, s.file)));
const totalFrames = todo.reduce((n, s) => n + s.frames[1] - s.frames[0], 0);
console.log(`${todo.length} sections to render (${totalFrames} frames) with ${opts.jobs} jobs`);

function render(section) {
    return new Promise((resolve, reject) => {
        const partial = path.join(outDir, section.file.replace(/\.mp4$/, '.partial.mp4'));
        const child = spawn(process.execPath, [
            path.join(__dirname, 'capture.js'),
            '--frames', section.frames.join(':'),
            '--fps', opts.fps,
            '--width', opts.width,
            '--aspect', opts.aspect,
            '--out', path.relative(ROOT, partial),
            ...passthrough,
        ], { cwd: ROOT, stdio: ['ignore', 'ignore', 'pipe'] });
        let errors = '';
        child.stderr.on('data', d => errors += d);
        const started = Date.now();
        child.on('close', code => {
            if (code !== 0) {
                reject(new Error(`section ${section.n} (${section.title}) failed: ${errors.trim()}`));
                return;
            }
            fs.renameSync(partial, path.join(outDir, section.file));
            console.log(`done ${section.file} (${formatTime(section.end - section.start)} in ${formatTime(Date.now() - started)})`);
            resolve();
        });
    });
}

(async () => {
    /* longest first, so a long section doesn't start last and hold everything up */
    const queue = todo.slice().sort((a, b) => (b.frames[1] - b.frames[0]) - (a.frames[1] - a.frames[0]));
    const failures = [];
    const worker = async () => {
        while (queue.length) {
            const section = queue.shift();
            try {
                await render(section);
            } catch (e) {
                failures.push(e.message);
                console.error(e.message);
            }
        }
    };
    await Promise.all(Array.from({ length: parseInt(opts.jobs, 10) }, worker));

    /* section index, and a concat list once every section exists */
    fs.writeFileSync(path.join(outDir, 'sections.json'), JSON.stringify(sections.map(s => ({
        n: s.n, title: s.title, start: formatTime(s.start), duration: formatTime(s.end - s.start), file: s.file,
    })), null, 1));
    const missing = sections.filter(s => !fs.existsSync(path.join(outDir, s.file)));
    if (!missing.length) {
        fs.writeFileSync(path.join(outDir, 'concat.txt'), sections.map(s => `file '${s.file}'`).join('\n') + '\n');
        console.log(`all sections rendered - join them with:\n  ffmpeg -f concat -safe 0 -i ${path.relative(ROOT, path.join(outDir, 'concat.txt'))} -c copy output/story-of-art.mp4`);
    } else {
        console.log(`${missing.length} sections still to render`);
    }
    process.exit(failures.length ? 1 : 0);
})();
