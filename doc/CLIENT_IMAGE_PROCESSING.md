# Client-Side Image Editing

This project renders the photo editor entirely in the browser: a `<canvas>`
holds the base image (webcam capture or upload), and stickers/text are
separate absolutely-positioned DOM elements drawn on top of it. When the user
hits Share, the canvas and overlay positions are packaged into JSON and sent
to `POST /api/photos`, where the server recreates the final image (see
`IMAGE_PROCESSING.md` for that half).

## Where it happens

The main client-side editing code lives in:

- `app/client/js/studio/StudioManager.js` — owns the canvas, webcam/upload
  lifecycle, capture, and the final share request.
- `app/client/js/studio/managers/StickerManager.js` — sticker placement,
  drag, and resize.
- `app/client/js/studio/managers/TextManager.js` — text overlay placement
  and editing.
- `app/client/js/studio/managers/FilterManager.js` — filter selection
  (CSS preview only).
- `app/client/js/studio/studioConfig.js` — shared runtime config
  (`canvasAspectRatio`, max sticker count, etc.), fetched once from
  `GET /api/studio-config`.

## Two coordinate spaces

The editor (`#studio-editor`) is a responsive element — it can be as wide as
the row layout allows on desktop, or the full viewport width when stacked on
mobile (see `ARCHITECTURE.md`'s container-query layout). This means there are
two different pixel scales in play at any moment, and the whole overlay
system exists to convert between them:

- **Canvas drawing-buffer pixels** — `canvas.width` / `canvas.height`. This
  is the actual resolution of the photo, fixed once measured (see below). It
  never changes just because the window resizes.
- **CSS-rendered pixels** — the editor's current on-screen size
  (`#studio-editor.getBoundingClientRect()`), which does change as the
  layout reflows.

Stickers and the text overlay are stored as **fractions (0–1) of the
editor's current rendered size**, not raw pixels, precisely so a value stays
meaningful in either space — multiply by the CSS rect for on-screen
placement, multiply by `canvas.width`/`canvas.height` for the payload sent
to the server.

## Canvas sizing

`#studio-editor` is `display: none` until the user activates the webcam or
uploads an image, and its width is a CSS percentage (`width: 100%;
aspect-ratio: 4 / 3;`), not a fixed value. Percentages can't be resolved on a
hidden element, so measuring too early locks the canvas to a 0×0 (or stale)
resolution for the rest of the session.

To avoid that, sizing is split into two steps in `StudioManager.js`:

```js
initCanvas() {
  this.canvasContext = this.editor.canvas.getContext('2d');
}

measureCanvas() {
  const computedStyle = getComputedStyle(this.editor.canvas);
  const cssWidth = parseInt(computedStyle.width);
  const cssHeight = parseInt(computedStyle.height);
  if (!cssWidth || !cssHeight) {
    return;
  }
  this.editor.canvas.width = cssWidth;
  this.editor.canvas.height = cssHeight;
  studioConfig.canvasAspectRatio = cssWidth / cssHeight;
}
```

`measureCanvas()` runs from `updateEditorView()`, but only on the actual
`menu → editor` transition:

```js
const wasHidden = this.editor.container.classList.contains('display-none');
this.editor.container.classList.remove('display-none');
...
if (wasHidden) {
  this.measureCanvas();
}
```

This guard matters: assigning to `canvas.width`/`canvas.height` always
clears the canvas, even to the same value. Re-running `measureCanvas()` on
every mode change (e.g. `webcam → captured` right after `capture()` draws a
frame) would silently wipe whatever was just drawn.

The canvas resolution is intentionally **not** re-measured on window resize
after that — only the overlay positions are (see Stickers, below). Keeping
the drawing buffer fixed avoids having to rescale or redraw whatever is
already on the canvas mid-edit.

## Capturing a photo

```js
capture(e) {
  const ctx = this.canvasContext;
  const width = this.editor.canvas.width;
  const height = this.editor.canvas.height;

  ctx.save();
  ctx.translate(width, 0);
  ctx.scale(-1, 1);
  ctx.drawImage(this.editor.video, 0, 0, width, height);
  ctx.restore();

  const data = this.editor.canvas.toDataURL('image/png');
  ...
}
```

`#webcam` is mirrored for the live preview with CSS (`transform:
scaleX(-1)`), but `drawImage()` reads the video element's underlying frame
data, which CSS transforms don't affect. The capture applies the same
horizontal flip directly on the canvas context so the saved photo matches
what the user saw in the preview, instead of coming out mirror-reversed
relative to it.

## Uploading a photo

`handleFileUpload()` reads the file with a `FileReader`, and once the image
element has decoded it, `drawUploadedImage()` paints it onto the same
canvas used for webcam capture:

```js
const imgAspectRatio = img.naturalWidth / img.naturalHeight;
const canvasAspectRatio = canvasWidth / canvasHeight;

let drawWidth, drawHeight;
if (imgAspectRatio > canvasAspectRatio) {
  drawWidth = canvasWidth * zoom;
  drawHeight = (canvasWidth / imgAspectRatio) * zoom;
} else {
  drawHeight = canvasHeight * zoom;
  drawWidth = canvasHeight * imgAspectRatio * zoom;
}
```

The image is scaled to fit entirely inside the canvas (matching whichever
dimension is the constraint, same idea as CSS `object-fit: contain`), then
`offsetX`/`offsetY`/`zoom` from `studioStore` let the user pan and zoom;
`drawUploadedImage()` re-runs on every change.

Because the canvas is always 4:3, an image with a different ratio (e.g. a
portrait upload) doesn't cover the whole canvas — it leaves the sides (or
top/bottom) unpainted. `drawUploadedImage()` fills the canvas with the
brand's lightest color (`--primary-100`, `#f2fcfa`) before drawing the
image, for two reasons: JPEG has no alpha channel, so `toDataURL('image/jpeg')`
would otherwise flatten that transparency to black on share; and filling
with a real color makes the live editor match what gets saved, instead of
the gaps only turning black after sharing.

## Stickers

`StickerManager.addSticker()` sizes a new sticker as a fraction of the
canvas, using the same "fit the constraining dimension" comparison as the
uploaded-image logic above, scaled to half the canvas:

```js
let widthFraction, heightFraction;
if (aspectRatio > this.config.canvasAspectRatio) {
  widthFraction = 0.5;
  heightFraction = 0.5 * (this.config.canvasAspectRatio / aspectRatio);
} else {
  heightFraction = 0.5;
  widthFraction = 0.5 * (aspectRatio / this.config.canvasAspectRatio);
}
```

New stickers always start at `xFraction: 0.25, yFraction: 0.25`.
`applyStickerGeometry()` converts a sticker's stored fractions into an
absolute CSS box on demand:

```js
applyStickerGeometry(overlay, stickerData) {
  const rect = this.editor.container.getBoundingClientRect();
  overlay.style.left = `${stickerData.xFraction * rect.width}px`;
  overlay.style.top = `${stickerData.yFraction * rect.height}px`;
  overlay.style.width = `${stickerData.widthFraction * rect.width}px`;
  overlay.style.height = `${stickerData.heightFraction * rect.height}px`;
}
```

Dragging and resizing (via `ToolManager.bindMouseInteraction()`) still work
in raw pixels while the pointer is moving — that part only needs the
editor's *current* rect, which `bindMouseInteraction()` reads fresh on
`pointerdown`. Only `onDragEnd` converts the final pixel position/size back
into a fraction before writing it to `studioStore`.

A `ResizeObserver` on `#studio-editor` re-applies every sticker's stored
fraction whenever the container's size actually changes:

```js
setupResizeObserver() {
  const observer = new ResizeObserver(() => this.repositionStickers());
  observer.observe(this.editor.container);
}
```

This is what keeps stickers visually anchored to the same spot on the photo
as the layout reflows between the row and stacked breakpoints, or as the
window is resized generally, instead of drifting or overflowing the editor.

## Text overlay

The text tool (`TextManager.js`) follows the same drag/click pattern via
`bindMouseInteraction()`, and now mirrors StickerManager's approach:
position is stored as `xFraction`/`yFraction` of the editor, reapplied in
pixels by `applyTextGeometry()`, and kept in sync by a `ResizeObserver` on
`#studio-editor` (`repositionText()`).

A new text overlay starts CSS-centered (`#text-preview-container` has
`left: 50%; top: 50%; transform: translate(-50%, -50%)`). `addText()` reads
that rendered position once, converts it into a fraction, then pins it with
an explicit `left`/`top` (removing the transform) so dragging and resizing
can take over from there.

`fontSize`, however, is stored as a raw px value — chosen from a fixed
dropdown and applied directly as the live CSS font size — rather than as a
fraction. That's fine for the on-screen preview (it's just sized against
whatever the editor's current rendered height happens to be), but it means
the same font-size number means something different in CSS-rendered space
versus the canvas's fixed drawing-buffer space. `sharePhoto()` corrects for
this at send time:

```js
const editorRect = this.editor.container.getBoundingClientRect();
fontSize: t.fontSize * (canvasHeight / editorRect.height),
```

scaling the chosen font size by how the editor's current height compares to
the canvas's actual resolution, so the text keeps the same proportion in
the final image as it had in the live preview.

## Filters

`FilterManager.applyFilter()` only ever sets a CSS `filter` on the canvas
element for a live preview:

```js
this.editor.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
```

This is a visual approximation only — the actual pixels are never touched
client-side. The real filter is applied server-side by `ImageComposer`
using GD's `imagefilter()`, by name (`selectedFilter`), which is a
different implementation and won't produce pixel-identical output to the
CSS preview.

## Sending to the server

`sharePhoto()` converts every sticker's stored fraction into pixels of the
canvas's drawing buffer (not the CSS-rendered size) before sending, since
that's the resolution the server-side compositor (`ImageComposer`) will
actually draw onto:

```js
const canvasWidth = this.editor.canvas.width;
const canvasHeight = this.editor.canvas.height;
const stickers = this.state.selectedStickers.map((sticker) => ({
  path: sticker.path,
  x: sticker.xFraction * canvasWidth,
  y: sticker.yFraction * canvasHeight,
  width: sticker.widthFraction * canvasWidth,
  height: sticker.heightFraction * canvasHeight,
}));

const finalImageData = {
  baseImage: this.editor.canvas.toDataURL('image/jpeg'),
  stickers,
  textOverlay: this.state.textOverlay,
  filter: this.state.selectedFilter,
};
```

`baseImage` is always read straight from the canvas — the Share button is
only ever visible in `captured` or `upload` mode, and both paths draw onto
the canvas before that point, so there's no case where it's empty at share
time.

## Notes

Reference information about current behavior and constraints.

- Sticker/text pixel coordinates sent to the server are computed against
  whatever `canvas.width`/`canvas.height` happened to be measured as (see
  Canvas sizing) — this is the same "coordinates must match the final
  canvas size" concern `IMAGE_PROCESSING.md` flags on the server side, just
  resolved on the client by normalizing to fractions first.
- The CSS `filter` preview and GD's `imagefilter()` result are two separate
  implementations by necessity. There's no CSS filter that reproduces GD's
  custom combos (e.g. sepia is grayscale + colorize, vintage stacks
  colorize/brightness/contrast/grayscale). Filter *names* are the only
  thing shared between them, so the live preview is always an
  approximation of the actual output, and the two can drift further out of
  visual sync if one side's filter recipe changes without the other.
  recipe changes without the other.
