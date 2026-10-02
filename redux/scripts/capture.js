/**
 * Renders The Story of Art to video
 *
 * Opens the player in headless Chrome, renders each frame at an exact time
 * with player.renderAt(), and pipes screenshots to ffmpeg. Rendering doesn't
 * need to keep up with real time, and long ranges can be split into chunks
 * and rendered in parallel, then joined with ffmpeg's concat demuxer.
 *
 * Usage:
 *   node scripts/capture.js --from 0 --to 30 --out output/test.mp4
 *   node scripts/capture.js --from 1h --to 2h --fps 50 --width 2048 --out output/part2.mp4
 *   node scripts/capture.js --stills 5,12.5,9h29m --out output/stills
 *
 * Times are seconds, or like 2h15m30s.
 */
const fs = require('fs');
const path = require('path');
const http = require('http');
const { spawn } = require('child_process');
const puppeteer = require('puppeteer-core');

const ROOT = path.resolve(__dirname, '..');
const CHROME = process.env.CHROME_PATH || '/usr/bin/google-chrome';

function parseTime(str) {
    if (/^[\d.]+$/.test(str)) return parseFloat(str) * 1000;
    const part = unit => parseFloat((str.match(new RegExp('([\\d.]+)' + unit)) || [, 0])[1]);
    return (part('h') * 3600 + part('m') * 60 + part('s')) * 1000;
}

function args() {
    const opts = { from: '0', to: '30', fps: '30', width: '1440', out: 'output/test.mp4', crf: '18' };
    const argv = process.argv.slice(2);
    for (let i = 0; i < argv.length; i += 2) {
        opts[argv[i].replace(/^--/, '')] = argv[i + 1];
    }
    return opts;
}

/* a minimal static server for the player (fetch() doesn't work from file://) */
function serve() {
    const types = { '.html': 'text/html', '.js': 'text/javascript', '.json': 'application/json' };
    const server = http.createServer((req, res) => {
        const file = path.join(ROOT, decodeURIComponent(new URL(req.url, 'http://x').pathname));
        if (!file.startsWith(ROOT) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) {
            res.writeHead(404).end();
            return;
        }
        res.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream' });
        fs.createReadStream(file).pipe(res);
    });
    return new Promise(resolve => server.listen(0, '127.0.0.1', () => resolve(server)));
}

(async () => {
    const opts = args();
    const width = parseInt(opts.width, 10);
    const height = Math.round(width * 3 / 4);
    const server = await serve();
    const browser = await puppeteer.launch({ executablePath: CHROME, headless: true, args: ['--hide-scrollbars'] });
    const page = await browser.newPage();
    await page.setViewport({ width, height });
    await page.goto(`http://127.0.0.1:${server.address().port}/index.html?capture`);
    await page.evaluate(() => window.playerReady);
    await page.evaluate(() => document.fonts.ready);
    const render = t => page.evaluate(t => window.player.renderAt(t), t);
    fs.mkdirSync(path.dirname(path.resolve(ROOT, opts.out)), { recursive: true });

    if (opts.stills) {
        const dir = path.resolve(ROOT, opts.out);
        fs.mkdirSync(dir, { recursive: true });
        for (const s of opts.stills.split(',')) {
            await render(parseTime(s));
            await page.screenshot({ path: path.join(dir, `still-${s}.png`) });
        }
        console.log(`stills written to ${dir}`);
    } else {
        const fps = parseFloat(opts.fps);
        const from = parseTime(opts.from);
        const to = parseTime(opts.to);
        const frames = Math.round((to - from) / 1000 * fps);
        const ffmpeg = spawn('ffmpeg', [
            '-y', '-loglevel', 'error',
            '-f', 'image2pipe', '-framerate', String(fps), '-c:v', 'png', '-i', '-',
            '-c:v', 'libx264', '-preset', 'slow', '-crf', opts.crf, '-pix_fmt', 'yuv420p', '-movflags', '+faststart',
            path.resolve(ROOT, opts.out),
        ], { stdio: ['pipe', 'inherit', 'inherit'] });
        const started = Date.now();
        for (let i = 0; i < frames; i++) {
            await render(from + i * 1000 / fps);
            const png = await page.screenshot({ type: 'png' });
            if (!ffmpeg.stdin.write(png)) {
                await new Promise(resolve => ffmpeg.stdin.once('drain', resolve));
            }
            if (i % Math.round(fps * 10) === 0) {
                process.stdout.write(`\r${i}/${frames} frames`);
            }
        }
        ffmpeg.stdin.end();
        await new Promise(resolve => ffmpeg.on('close', resolve));
        const secs = (Date.now() - started) / 1000;
        console.log(`\r${frames} frames in ${secs.toFixed(0)}s (${(frames / secs).toFixed(1)} fps) -> ${opts.out}`);
    }
    await browser.close();
    server.close();
})();
