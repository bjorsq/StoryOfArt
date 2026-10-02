/**
 * The Story of Art player
 *
 * Renders any moment of the piece from the timeline (timeline.json, made by
 * scripts/extract-timeline.js). Only one text box is ever in the document,
 * so memory use stays flat however long the piece runs.
 *
 * The text layout is a port of TextSpline (soa_multiples.es) and the motion
 * reproduces the SMIL animation from the original files:
 *  - zoom in:  font-size 0em -> 1em, moving from 50px above to the rest position
 *  - zoom out: font-size 1em -> 20em, moving 950px down (headings move up)
 * each eased with the original keySplines.
 */
const SVG_NS = 'http://www.w3.org/2000/svg';
/* the Adobe SVG Viewer's default font size, which the original layout assumed */
const BASE_FONT_SIZE = 12;
/* TextSpline line width, multiplied by the font size */
const DEFAULT_WIDTH = 420;

class StoryOfArt {
    constructor(svg, timeline) {
        this.timeline = timeline;
        this.boxes = timeline.boxes;
        this.screen = timeline.screen;
        this.easings = timeline.splines.map(set => set.map(s => cubicBezier(...s)));
        this.group = document.createElementNS(SVG_NS, 'g');
        this.text = document.createElementNS(SVG_NS, 'text');
        this.group.appendChild(this.text);
        svg.appendChild(this.group);
        this.current = null;
    }

    get duration() {
        return this.timeline.duration;
    }

    /* the box on screen at time t (ms), or null between boxes */
    boxAt(t) {
        let lo = 0;
        let hi = this.boxes.length - 1;
        while (lo <= hi) {
            const mid = (lo + hi) >> 1;
            const box = this.boxes[mid];
            if (t < box.start) {
                hi = mid - 1;
            } else if (t >= box.start + box.in + (box.pause || 0) + box.out) {
                lo = mid + 1;
            } else {
                return box;
            }
        }
        return null;
    }

    /* render the piece at time t (ms) - the piece loops */
    renderAt(t) {
        t = ((t % this.duration) + this.duration) % this.duration;
        const box = this.boxAt(t);
        if (!box) {
            this.group.setAttribute('visibility', 'hidden');
            return;
        }
        if (box !== this.current) {
            this.layout(box);
            this.current = box;
        }
        const [scaleIn, moveIn, scaleOut, moveOut] = this.easings[box.splines];
        const local = t - box.start;
        const pause = box.pause || 0;
        let em;
        let y;
        if (local < box.in) {
            const p = local / box.in;
            em = scaleIn(p);
            y = -50 + 50 * moveIn(p);
        } else if (local < box.in + pause) {
            em = 1;
            y = 0;
        } else {
            const p = (local - box.in - pause) / box.out;
            em = 1 + 19 * scaleOut(p);
            y = box.exit * moveOut(p);
        }
        this.group.setAttribute('visibility', 'visible');
        this.group.style.fontSize = (em * BASE_FONT_SIZE) + 'px';
        this.group.setAttribute('transform', 'translate(0 ' + y + ')');
    }

    /**
     * Port of TextSpline._splitString and TextSpline._layout: words are
     * wrapped by measuring the text, then each line is justified with
     * word-spacing, and the last line is aligned left
     */
    layout(box) {
        const text = this.text;
        const size = box.size;
        const width = size * DEFAULT_WIDTH;
        const em = size * BASE_FONT_SIZE;
        /* measure at the resting size */
        this.group.style.fontSize = BASE_FONT_SIZE + 'px';
        text.style.fontSize = size + 'em';
        text.removeAttribute('text-anchor');
        text.replaceChildren(document.createTextNode(''));
        const measure = text.firstChild;

        const words = box.text.split(' ');
        const lines = [];
        let line = [];
        let length = 0;
        let prevLength = 0;
        while (words.length) {
            const word = words[0];
            measure.data = line.join(' ') + ' ' + word;
            length = text.getComputedTextLength();
            if ((length > width && line.length) || word === '_br_') {
                lines.push({ width: prevLength, words: line });
                line = [];
                if (word === '_br_') {
                    words.shift();
                }
            } else {
                line.push(words.shift());
            }
            prevLength = length;
            if (!words.length) {
                lines.push({ width: length, words: line });
            }
        }

        const x = this.screen.width / 2;
        let y = (this.screen.height / 2) - (lines.length * em);
        text.replaceChildren();
        lines.forEach((ln, i) => {
            let dx = 0;
            let spacing = 'normal';
            if (i + 1 === lines.length) {
                if (i !== 0) {
                    dx = -((width - ln.width) / 2) - 1;
                } else {
                    /* single line */
                    y -= size * box.interval * BASE_FONT_SIZE;
                }
            } else if (ln.words.length > 1) {
                spacing = ((width - ln.width) / (ln.words.length - 1)) / em + 'em';
            }
            const tspan = document.createElementNS(SVG_NS, 'tspan');
            tspan.textContent = ln.words.join(' ');
            tspan.style.wordSpacing = spacing;
            tspan.setAttribute('x', 0);
            tspan.setAttribute('dx', (StoryOfArt.lastLineShift * dx) / em + 'em');
            tspan.setAttribute('dy', box.interval + 'em');
            text.appendChild(tspan);
        });
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('transform', 'translate(' + x + ' ' + y + ')');
    }
}
/* the original doubled the last line's offset, which the Adobe SVG Viewer must have halved when
 * anchoring the text - modern browsers apply dx in full, so 1 gives the intended left-aligned last line */
StoryOfArt.lastLineShift = 1;

/**
 * Cubic bezier easing, as used by SMIL keySplines (and CSS cubic-bezier)
 */
function cubicBezier(x1, y1, x2, y2) {
    const cx = 3 * x1, bx = 3 * (x2 - x1) - cx, ax = 1 - cx - bx;
    const cy = 3 * y1, by = 3 * (y2 - y1) - cy, ay = 1 - cy - by;
    const sampleX = s => ((ax * s + bx) * s + cx) * s;
    const sampleY = s => ((ay * s + by) * s + cy) * s;
    const slopeX = s => (3 * ax * s + 2 * bx) * s + cx;
    return function (x) {
        if (x <= 0) return 0;
        if (x >= 1) return 1;
        /* Newton's method, falling back to bisection */
        let s = x;
        for (let i = 0; i < 8; i++) {
            const err = sampleX(s) - x;
            if (Math.abs(err) < 1e-6) return sampleY(s);
            const d = slopeX(s);
            if (Math.abs(d) < 1e-6) break;
            s -= err / d;
        }
        let lo = 0, hi = 1;
        s = x;
        while (hi - lo > 1e-7) {
            if (sampleX(s) < x) lo = s; else hi = s;
            s = (lo + hi) / 2;
        }
        return sampleY(s);
    };
}
