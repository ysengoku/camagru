# Image Processing

This project renders the photo editor entirely in the browser, then recreates
the final image on the server:

1. [**Client**](#1-client-side-image-editing): a `<canvas>` holds the base image (webcam capture or upload), and stickers/text are separate absolutely-positioned DOM elements drawn on top of it. When the user hits Share, the canvas is converted to a JPEG blob and sent via POST to `/api/photos`, along with sticker, text, and filter metadata.

2. [**Server**](#2-server-side-image-processing-gd): PHP's GD extension recreates the final image from that JSON: stickers, optional filters, and text are composited onto the base image and saved as a JPEG.

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

The editor (`#studio-editor`) is responsive, so its on-screen size changes as the layout reflows. This creates two different pixel scales that the overlay system has to convert between:

- **Canvas drawing-buffer pixels** (`canvas.width` / `canvas.height`): the
  actual resolution of the photo. Fixed once measured ([see below](#canvas-sizing)) and
  never changes on resize.
- **CSS-rendered pixels** (`#studio-editor.getBoundingClientRect()`): the
  editor's current on-screen size, which does change as the layout reflows.

Stickers and the text overlay are stored as **fractions (0-1) of the editor's current rendered size**, not raw pixels, precisely so that a value stays meaningful in either space: multiply by the CSS rect for on-screen placement, multiply by `canvas.width`/`canvas.height` for the payload sent to the server.

### Canvas sizing

`#studio-editor` stays hidden (`display: none`) until the user starts the webcam or uploads an image. Once it becomes visible, `StudioManager.js` measures it in `measureCanvas()`.

This has to wait for visibility: the editor's size isn't a fixed pixel value, it comes from CSS (`flex: 1 1 auto`, `aspect-ratio: 16 / 9`, `width`/`height: auto`, capped by `max-width`/`max-height: 100%`), and none of that resolves on a hidden element. Measuring too early would lock the canvas to a 0×0 (or stale) resolution for the rest of the session.

```js
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

`measureCanvas()` runs from `updateEditorView()`, and only on the actual
`menu → editor` transition:

```js
const wasHidden = this.editor.container.classList.contains('display-none');
this.editor.container.classList.remove('display-none');
...
if (wasHidden) {
  this.measureCanvas();
}
```

Assigning to `canvas.width`/`canvas.height` always clears the canvas, even　when set to the same value. That's why the guard matters. Without it,　`measureCanvas()` would re-run on every mode change, for example right　after `capture()` draws a frame during `webcam → captured`, and silently　discard whatever was just drawn.

For the same reason, the canvas resolution is never re-measured when the window resizes. Only the overlay positions are (see [Stickers](#stickers), below). Keeping the drawing buffer fixed means the app never has to rescale or redraw whatever is already on the canvas mid-edit.

### Capturing a photo

`capture()` crops the live video to match the canvas's aspect ratio, mirrors it to match the live preview, and draws the result onto the canvas before switching to `captured` mode:

```js
capture(e) {
  const ctx = this.canvasContext; // this.editor.canvas.getContext('2d')
  const width = this.editor.canvas.width;
  const height = this.editor.canvas.height;
  ...
  const scale = Math.max(width / videoWidth, height / videoHeight);
  const scaledWidth = width / scale;
  const scaledHeight = height / scale;
  const scaledX = (videoWidth - scaledWidth) / 2;
  const scaledY = (videoHeight - scaledHeight) / 2;

  ctx.save(); // snapshot context state to restore later
  ctx.translate(width, 0); // move origin to canvas's right edge
  ctx.scale(-1, 1); // flip horizontally
  ctx.drawImage(
    video,
    scaledX,
    scaledY,
    scaledWidth,
    scaledHeight,
    0,
    0,
    width,
    height
  );
  ctx.restore();

  ...
}
```

The video's native resolution (`videoWidth`/`videoHeight`) doesn't necessarily match the canvas's aspect ratio, so drawing it directly would stretch and distort the image.

`capture()` first works out a `cover`-style crop, the same idea as CSS `object-fit: cover`. `scale` is the larger of the two ratios needed to fill the canvas in each dimension, and `scaledX`/`scaledY` center a crop of that size within the source video.

`drawImage()` then reads that cropped rectangle from the video and draws it to fill the whole canvas, so that the result matches the canvas's aspect ratio with nothing stretched, only cropped.

`#webcam` is mirrored for the live preview with CSS (`transform: scaleX(-1)`), but `drawImage()` reads the video element's underlying frame data, which CSS transforms don't affect. The capture applies the same horizontal flip directly on the canvas context so the saved photo matches what the user saw in the preview.

### Uploading a photo

`handleFileUpload()` reads the file with a `FileReader`, and once the image element has decoded it, `drawUploadedImage()` paints it onto the same canvas used for webcam capture:

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

The image is scaled to fit entirely inside the canvas (matching whichever　dimension is the constraint), the same idea as CSS `object-fit: contain`.　`drawUploadedImage()` re-runs whenever the uploaded image changes.

Because the canvas is 16:9, an image with a different ratio (e.g. a　portrait upload) doesn't cover the whole canvas. It leaves the sides (or
top/bottom) unpainted.  
`drawUploadedImage()` fills the canvas with the brand's lightest color before drawing the
image.

### Stickers

Stickers are positioned and sized as **fractions (0-1) of the editor's current rendered size**, not raw pixels (see Two coordinate spaces above).  
`applyStickerGeometry()` converts a sticker's stored fractions into an absolute CSS box on demand:

```js
applyStickerGeometry(overlay, stickerData) {
  const rect = this.editor.container.getBoundingClientRect();
  overlay.style.left = `${stickerData.xFraction * rect.width}px`;
  overlay.style.top = `${stickerData.yFraction * rect.height}px`;
  overlay.style.width = `${stickerData.widthFraction * rect.width}px`;
  overlay.style.height = `${stickerData.heightFraction * rect.height}px`;
}
```

### Sticker position

New stickers always start at `xFraction: 0.25, yFraction: 0.25`.

Dragging (via `ToolManager.bindMouseInteraction()`) works in raw pixels while the pointer is moving. That part only needs the editor's _current_ rect, which `bindMouseInteraction()` reads fresh on `pointerdown`. Only `onDragEnd` converts the final pixel position back into a fraction before writing it to `studioStore`.

### Sticker size

A new sticker starts at half the editor's size, using the same fit-to-aspect-ratio logic as the uploaded image above:

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

Resizing via the sticker's resize handle works the same way as dragging above: `onDragMove` sets the width/height in raw pixels while the pointer moves, and `onDragEnd` converts the final size back into `widthFraction`/`heightFraction` before writing it to `studioStore`.

A `ResizeObserver` on `#studio-editor` re-applies every sticker's stored fractions whenever the container's size actually changes, keeping stickers visually anchored to the same spot on the photo as the layout reflows or the window resizes, instead of drifting or overflowing the editor:

```js
setupResizeObserver() {
  const observer = new ResizeObserver(() => this.repositionStickers());
  observer.observe(this.editor.container);
}
```

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
````

scaling the chosen font size by how the editor's current height compares to
the canvas's actual resolution, so the text keeps the same proportion in
the final image as it had in the live preview.

### Filters

`FilterManager.applyFilter()` only ever sets a CSS `filter` on the canvas
element for a live preview:

```js
this.editor.canvas.style.filter = selectedFilterObj?.filterValue || "none";
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
  this.editor.canvas.toBlob(resolve, "image/jpeg"),
);
const finalImageData = new FormData();
finalImageData.append("image", imageBlob, "photo.jpg");
finalImageData.append(
  "data",
  JSON.stringify({
    stickers,
    textOverlay,
    filter: this.state.selectedFilter,
  }),
);
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
  _names_ are the only thing shared between them, so the live preview is
  always an approximation of the actual output, and the two can drift
  further out of visual sync if one side's filter recipe changes without
  the other.

## 2. Server-Side Image Processing (GD)

This project uses PHP's GD extension to compose the final photo on the server.
The browser sends a base image and the selected editing data to the API, then
the backend recreates the final image with stickers, optional filters and text.

The main image processing code lives in:

- `app/src/Controllers/API/PhotoApiController.php`
- `app/src/Services/ImageComposer.php`

`PhotoApiController` receives the API request, validates the required data,
chooses a filename in the media directory, and calls `ImageComposer`.

`ImageComposer` owns the GD image resource and applies the visual changes.

### Input data

The frontend sends a `multipart/form-data` request to `POST /api/photos`.  
It has two parts:

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

Fonts are stored in `public/assets/fonts`.
The client sends a font family, and the backend maps it to a font file.

The text color is sent as a hex string, for example `#adffff`. The backend
converts it into RGB values for GD.

GD's font size number does not match a CSS `font-size` of the same value.   
GD renders noticeably larger. GD treats its size number as points at 96dpi, while the browser renders CSS pixels directly, and 1 point is 1/72 inch. So a GD size of 72 renders the same as a CSS `font-size` of 96, a ratio of `72 / 96`. The size sent from the client is scaled down by that ratio before it reaches GD:

```php
$fontSize = $cssFontSize * (72.0 / 96.0);
```

Then the text is drawn:

```php
imagefttext(
    $this->canvas,
    $fontSize,
    0.0,
    $x,
    $baselineY,
    $color,
    $fontPath,
    $content
);
```

`imagefttext()` positions text by its **baseline**, not the top of the
glyphs, but `x`/`y` arrive from the client as the top left corner of the CSS
text box shown in the editor preview (see Section 1, Text overlay, above).
Turning "top of box" into "baseline" needs to know how much space the font
reserves above the baseline. That space depends on the font itself, not on
which letters were actually typed, so it is read straight from the font
file rather than measured from the typed content:

```php
[$lineAscent, $lineDescent] = $this->getLineMetrics($fontPath, $cssFontSize);
$baselineY = ($textOverlay['y'] ?? 0) + $lineAscent;
```

`x` and `baselineY` are then kept within the canvas bounds, so text placed
near an edge in the preview cannot be cut off in the final image. `x` is
checked against `imagettfbbox()` for the actual typed content, while
`baselineY` is checked against the same `lineAscent`/`lineDescent` used
above, since those already describe the space the browser reserves.

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
