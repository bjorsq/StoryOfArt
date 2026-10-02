The Story of Art - redux
========================

A redevelopment of _The Story of Art_ (Emma Kay, with e-2) for modern browsers, and for rendering to video.

The original ran in the Adobe SVG Viewer 3. It no longer runs in modern browsers: the files use an undeclared `xlink` namespace (so they don't parse), the layout script reads invalid CSS from `style` attributes (which browsers now discard), and the text was set in an embedded SVG font (only Safari supports these).

How it works
------------

`scripts/extract-timeline.js` reads the Tate version (`../tate version/soa1.svg` to `soa8.svg`, in the order they were played at the Tate) and writes `timeline.json`: the text, font size and line spacing of each of the 5,812 text boxes, and the durations and easing of its animation. The piece runs for 9h 30m.

`player.js` renders any moment of the piece from the timeline. Only one text box is in the document at a time, so it can run indefinitely without using more memory. The text layout is a port of `TextSpline` from `soa_multiples.es`, and the motion reproduces the SMIL animation:

* zoom in: font size 0em to 1em, moving down 50px to the rest position
* zoom out: font size 1em to 20em, moving 950px down (section headings move up by font size x 1000px)

each eased with the original `keySplines`.

```
npm install
npm run timeline      # regenerate timeline.json
```

### Playing in a browser

Serve this folder (e.g. `npx http-server .`) and open `index.html`.

* `?t=2h15m` starts at that point (or plain seconds)
* `?debug` shows the time
* space pauses, left and right arrows skip 10 seconds (60 with shift)

The piece loops, as it did at the Tate.

### Rendering video

`scripts/capture.js` opens the player in headless Chrome, renders each frame at an exact time and pipes it to ffmpeg, so rendering doesn't need to keep up with real time.

```
node scripts/capture.js --from 0 --to 30 --out output/test.mp4
node scripts/capture.js --from 1h --to 2h --fps 50 --width 2048 --out output/part2.mp4
node scripts/capture.js --stills 5,9258.05 --out output/stills
```

Defaults are 30fps at 1440x1080. Rendering runs at about 20 frames per second, so the whole piece at 30fps (about 1 million frames) would take around 14 hours in one process. Split it into ranges, render them in parallel, and join them with ffmpeg's concat demuxer.

Notes on fidelity
-----------------

* **Font**: the embedded "pm" font has Arial's PANOSE number, metrics and glyph widths, so the player uses Arial, falling back to Liberation Sans or Arimo, which have the same metrics (so lines wrap identically).
* **Colour**: the original set no colours, so it was black text on the viewer's default white background.
* **Font size**: the layout assumed 1em = 12px (the Adobe viewer's default), so the player sets that explicitly.
* **Last line of a paragraph**: `TextSpline` doubled the offset used to left-align the last line, which must have been halved by the Adobe viewer. Modern browsers apply it in full, so the player uses the single offset (`StoryOfArt.lastLineShift`), which gives the intended left alignment.
* **Joins between files**: the original loaded the next file when a file's last box finished. The player treats the eight files as one continuous sequence, so there are no gaps for loading.
