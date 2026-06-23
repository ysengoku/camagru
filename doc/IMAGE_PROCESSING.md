# Image Processing with GD

This project uses PHP's GD extension to compose the final photo on the server.
The browser sends a base image and the selected editing data to the API, then
the backend recreates the final image with stickers, optional filters and text.

## Where it happens

The main image processing code lives in:

- `app/src/Controllers/PhotoApiController.php`
- `app/src/helper/ImageComposer.php`

`PhotoApiController` receives the API request, validates the required data,
chooses a filename in the media directory, and calls `ImageComposer`.

`ImageComposer` owns the GD image resource and applies the visual changes.

## Input data

The frontend sends JSON to `POST /api/photos`.

Typical payload:

```json
{
  "baseImage": "data:image/jpeg;base64,...",
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

Coordinates and sizes are currently sent in pixels from the editor UI.

## Processing flow

1. Decode the base image.
2. Create a GD image resource with `imagecreatefromstring()`.
3. Store the canvas width and height with `imagesx()` and `imagesy()`.
4. Apply the selected filter.
5. Load, resize, and copy each sticker onto the canvas.
6. Draw the optional text overlay with a TrueType font.
7. Save the final image as a JPEG in the media directory.

In code, this is coordinated by:

```php
$imageComposer = new ImageComposer($baseImage);
$saved = $imageComposer->compose($stickers, $text ?? [], $filter, $imagePath);
```

## Base image

The base image arrives as a data URL:

```text
data:image/jpeg;base64,...
```

Before GD can read it, the data URL prefix is removed and the remaining base64
string is decoded:

```php
$imageData = base64_decode(
    preg_replace('/^data:image\/\w+;base64,/i', '', $base64Image)
);
```

Then GD creates the canvas:

```php
$this->canvas = imagecreatefromstring($imageData);
```

If the base64 data is invalid, `ImageComposer` throws an exception.

## Filters

Filters use GD's `imagefilter()` function.

Supported filters:

- `none`
- `grayscale`
- `sepia`
- `vintage`
- `dream`

Examples:

```php
imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
imagefilter($this->canvas, IMG_FILTER_COLORIZE, 112, 66, 20);
```

Some filters are made by combining multiple GD filters, such as grayscale,
brightness, contrast, blur, and colorize.

## Stickers

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

## Text overlay

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

## Saving

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

## Error handling

The controller checks common failure cases:

- missing base image
- missing stickers
- media directory not writable
- image creation exception
- failed image save

When image processing fails, the API returns a JSON error response instead of
silently succeeding.

## Notes

- GD works directly with pixels, so frontend coordinates must match the final
  canvas size or be converted before drawing.
- If the responsive UI changes the displayed editor size, normalized
  coordinates or backend scaling may be needed.
- The GD image is stored as a `GdImage` object. PHP releases it when the
  `ImageComposer` object is no longer used.
