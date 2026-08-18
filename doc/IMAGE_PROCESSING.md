# Image Processing

This project renders the photo editor entirely in the browser, then recreates
the final image on the server:

1. **Client**: a `<canvas>` holds the base image (webcam capture or upload),
   and stickers/text are separate absolutely-positioned DOM elements drawn on
   top of it. When the user hits Share, the canvas and overlay positions are
   packaged into JSON and sent to `POST /api/photos`.
2. **Server**: PHP's GD extension recreates the final image from that JSON:
   stickers, optional filters, and text are composited onto the base image
   and saved as a JPEG.

## 1. Client-Side Image Editing

The main client-side editing code lives in:

- `app/client/js/studio/StudioManager.js`: owns the canvas, webcam/upload
  lifecycle, capture, and the final share request.
- `app/client/js/studio/managers/StickerManager.js`: sticker placement,
  drag, and resize.
- `app/client/js/studio/managers/TextManager.js`: text overlay placement
  and editing.
- `app/client/js/studio/managers/FilterManager.js`: filter selection
  (CSS preview only).
- `app/client/js/studio/studioConfig.js`: shared runtime config
  (`canvasAspectRatio`, max sticker count, etc.), fetched once from
  `GET /api/studio-config`.

### Two coordinate spaces

The editor (`#studio-editor`) is a responsive element: it can be as wide as
the row layout allows on desktop, or the full viewport width when stacked on
mobile (see `ARCHITECTURE.md`'s container-query layout). This means there are
two different pixel scales in play at any moment, and the whole overlay
system exists to convert between them:

- **Canvas drawing-buffer pixels**: `canvas.width` / `canvas.height`. This
  is the actual resolution of the photo, fixed once measured (see below). It
  never changes just because the window resizes.
- **CSS-rendered pixels**: the editor's current on-screen size
  (`#studio-editor.getBoundingClientRect()`), which does change as the
  layout reflows.

Stickers and the text overlay are stored as **fractions (0-1) of the
editor's current rendered size**, not raw pixels, precisely so a value stays
meaningful in either space: multiply by the CSS rect for on-screen
placement, multiply by `canvas.width`/`canvas.height` for the payload sent
to the server.

### Canvas sizing

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
after that; only the overlay positions are (see Stickers, below). Keeping
the drawing buffer fixed avoids having to rescale or redraw whatever is
already on the canvas mid-edit.

### Capturing a photo

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

  ...
}
```

`#webcam` is mirrored for the live preview with CSS (`transform:
scaleX(-1)`), but `drawImage()` reads the video element's underlying frame
data, which CSS transforms don't affect. The capture applies the same
horizontal flip directly on the canvas context so the saved photo matches
what the user saw in the preview, instead of coming out mirror-reversed
relative to it.

### Uploading a photo

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
portrait upload) doesn't cover the whole canvas; it leaves the sides (or
top/bottom) unpainted. `drawUploadedImage()` fills the canvas with the
brand's lightest color (`--primary-100`, `#f2fcfa`) before drawing the
image, for two reasons: JPEG has no alpha channel, so `toDataURL('image/jpeg')`
would otherwise flatten that transparency to black on share; and filling
with a real color makes the live editor match what gets saved, instead of
the gaps only turning black after sharing.

### Stickers

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
in raw pixels while the pointer is moving; that part only needs the
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

### Text overlay

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

`fontSize`, however, is stored as a raw px value (chosen from a fixed
dropdown and applied directly as the live CSS font size) rather than as a
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

### Filters

`FilterManager.applyFilter()` only ever sets a CSS `filter` on the canvas
element for a live preview:

```js
this.editor.canvas.style.filter = selectedFilterObj?.filterValue || 'none';
```

This is a visual approximation only; the actual pixels are never touched
client-side. The real filter is applied server-side by `ImageComposer`
using GD's `imagefilter()`, by name (`selectedFilter`), which is a
different implementation and won't produce pixel-identical output to the
CSS preview (see Section 2, Filters, below).

### Sending to the server

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

const imageBlob = await new Promise((resolve) =>
  this.editor.canvas.toBlob(resolve, 'image/jpeg')
);
const finalImageData = new FormData();
finalImageData.append('image', imageBlob, 'photo.jpg');
finalImageData.append('data', JSON.stringify({
  stickers,
  textOverlay,
  filter: this.state.selectedFilter,
}));
```

### Client-side notes

Reference information about current behavior and constraints.

- Sticker/text pixel coordinates sent to the server are computed against
  whatever `canvas.width`/`canvas.height` happened to be measured as (see
  Canvas sizing): this is the same "coordinates must match the final
  canvas size" concern Section 2 flags on the server side, just resolved on
  the client by normalizing to fractions first.
- The CSS `filter` preview and GD's `imagefilter()` result are two separate
  implementations by necessity. There's no CSS filter that reproduces GD's
  custom combos (e.g. sepia is grayscale + colorize, vintage and polaroid
  stack colorize/brightness/contrast over a partial grayscale). Filter
  *names* are the only thing shared between them, so the live preview is
  always an approximation of the actual output, and the two can drift
  further out of visual sync if one side's filter recipe changes without
  the other.

## 2. Server-Side Image Processing (GD)

This project uses PHP's GD extension to compose the final photo on the server.
The browser sends a base image and the selected editing data to the API, then
the backend recreates the final image with stickers, optional filters and text.

### Where it happens

The main image processing code lives in:

- `app/src/Controllers/API/PhotoApiController.php`
- `app/src/Services/ImageComposer.php`

`PhotoApiController` receives the API request, validates the required data,
chooses a filename in the media directory, and calls `ImageComposer`.

`ImageComposer` owns the GD image resource and applies the visual changes.

### Input data

The frontend sends a `multipart/form-data` request to `POST /api/photos`, not
JSON. It has two parts:

- `image`: the JPEG blob produced by `canvas.toBlob()`.
- `data`: a JSON string with everything else.

Typical `data` part:

```json
{
  "stickers": [
    {
      "path": "/assets/stickers/flower1.png",
      "x": 160,
      "y": 120,
      "width": 320,
      "height": 175
    }
  ],
  "textOverlay": {
    "content": "Hello",
    "fontFamily": "Pacifico",
    "fontSize": "40",
    "color": "#adffff",
    "x": 200,
    "y": 180
  },
  "filter": "none"
}
```

The controller reads the file from `$_FILES['image']` and decodes the `data`
part with `json_decode()`.

Coordinates and sizes are currently sent in pixels from the editor UI.

### Processing flow

1. Read the uploaded image file.
2. Create a GD image resource with `imagecreatefromstring()`.
3. Store the canvas width and height with `imagesx()` and `imagesy()`.
4. Apply the selected filter.
5. Load, resize, and copy each sticker onto the canvas.
6. Draw the optional text overlay with a TrueType font.
7. Save the final image as a JPEG in the media directory.

In code, this is coordinated by:

```php
$imageComposer = new ImageComposer($uploadedFile['tmp_name']);
$saved = $imageComposer->compose($stickers, $text ?? [], $filter, $imagePath);
```

### Base image

The base image arrives as an uploaded file, not JSON. `ImageComposer`'s
constructor takes the uploaded file's temporary path and reads it directly:

```php
$imageData = file_get_contents($imagePath);
```

Then GD creates the canvas from the raw bytes:

```php
$this->canvas = imagecreatefromstring($imageData);
```

If the image data is invalid, `ImageComposer` throws an exception.

### Filters

Filters use GD's `imagefilter()` function.

Supported filters:

- `none`
- `grayscale`
- `sepia`
- `vintage`
- `polaroid`

Examples:

```php
imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
imagefilter($this->canvas, IMG_FILTER_COLORIZE, 112, 66, 20);
```

Some filters are made by combining multiple GD filters, such as a partial
grayscale blend, colorize, brightness, and contrast.

Because the CSS preview (Section 1, Filters) and GD's `imagefilter()` are
different implementations, matching a new filter's look on both sides means
sticking to effects GD can express natively: `hue-rotate` in particular has
no GD equivalent, which is why filter presets are designed around
brightness/contrast/grayscale/colorize rather than arbitrary hue shifts.

### Stickers

Stickers are stored as PNG files in `public/assets/stickers`.

For each sticker:

1. The backend receives the sticker path and position.
2. GD loads the PNG with `imagecreatefrompng()`.
3. The sticker is resized with `imagescale()`.
4. The resized sticker is copied onto the base canvas with `imagecopy()`.

Simplified example:

```php
$stickerImage = imagecreatefrompng($stickerPath);
$resizedSticker = imagescale($stickerImage, $width, $height);

imagecopy(
    $this->canvas,
    $resizedSticker,
    $x,
    $y,
    0,
    0,
    imagesx($resizedSticker),
    imagesy($resizedSticker)
);
```

### Text overlay

Text is drawn with `imagefttext()`, which supports TrueType fonts.

Fonts are stored in:

```text
public/assets/fonts
```

The frontend sends a font family, and the backend maps it to a font file:

```php
const FONT_MAP = [
    'Raleway' => 'Raleway-Regular.ttf',
    'Pacifico' => 'Pacifico-Regular.ttf'
];
```

The text color is sent as a hex string, for example `#adffff`. The backend
converts it into RGB values for GD:

```php
$hexColor = ltrim($textOverlay['color'] ?? '#001919', '#');

$color = imagecolorallocate(
    $this->canvas,
    hexdec(substr($hexColor, 0, 2)),
    hexdec(substr($hexColor, 2, 2)),
    hexdec(substr($hexColor, 4, 2))
);
```

Then the text is drawn:

```php
imagefttext(
    $this->canvas,
    (float) $fontSize,
    0.0,
    $x,
    $y,
    $color,
    $fontPath,
    $content
);
```

`imagefttext()` positions text by its **baseline**, not the top of the
glyphs, but `x`/`y` arrive from the client as the top-left corner of the CSS
text box shown in the editor preview (see Section 1, Text overlay, above).
Passing that `y` straight through would draw text lower than the preview
showed, and clip ascenders off entirely for text placed near the top edge.
`imagettfbbox()` gives the font's ascent for the given size/content, which
is added to `y` before drawing to convert "top of box" into "baseline":

```php
$bbox = imagettfbbox((float)$fontSize, 0.0, $fontPath, $content);
$ascent = -$bbox[7];
```

### Saving

The final image is saved as a JPEG:

```php
imagejpeg($this->canvas, $filePath);
```

In Docker, the app writes generated images to the shared media volume. Nginx
serves the same files through the public `/media/...` URL.

The API returns the public path:

```json
{
  "message": "Post created successfully",
  "data": {
    "path": "/media/generated-file.jpg"
  }
}
```

### Error handling

The controller checks common failure cases:

- missing base image
- missing stickers
- media directory not writable
- image creation exception
- failed image save

When image processing fails, the API returns a JSON error response instead of
silently succeeding.

### Server-side notes

- GD works directly with pixels, so frontend coordinates must match the final
  canvas size or be converted before drawing.
- If the responsive UI changes the displayed editor size, normalized
  coordinates or backend scaling may be needed.
- The GD image is stored as a `GdImage` object. PHP releases it when the
  `ImageComposer` object is no longer used.
