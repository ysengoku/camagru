<?php
const FONT_MAP = [
    'Raleway' => 'Raleway-Regular.ttf',
    'Bebas Neue' => 'BebasNeue-Regular.ttf',
    'HomemadeApple' => 'HomemadeApple-Regular.ttf',
    'Lexend' => 'Lexend-VariableFont_wght.ttf',
    'Pacifico' => 'Pacifico-Regular.ttf',
    'Playfair Display' => 'PlayfairDisplay-VariableFont_wght.ttf'
];

final class ImageComposer {
    private \GdImage $canvas;

    private string $publicPath;
    private string $fontsDirPath;

    /**
     * @param string $base64Image The base64-encoded image data.
     * @throws \InvalidArgumentException If the base64 image data is invalid.
     * @throws \RuntimeException If the public path cannot be resolved.
     */
    public function __construct(string $base64Image) {
        $replaced = preg_replace('/^data:image\/\w+;base64,/i', '', $base64Image);
        $imageData = base64_decode($replaced ?? '');

        $canvas = imagecreatefromstring($imageData);
        if (!$canvas) {
            throw new \InvalidArgumentException("Invalid base64 image data.");
        }
        $this->canvas = $canvas;

        $publicPath = realpath(Path::getPublicPath());
        if ($publicPath === false) {
            throw new \RuntimeException("Public path could not be resolved.");
        }
        $this->publicPath = $publicPath;
        $this->fontsDirPath = Path::join($this->publicPath, 'assets', 'fonts');
    }

    /**
         * Composes the final image by applying stickers, text overlay, and filter, then saves it to the specified file path.
         * @param list<array{
         * path: string,
         * width: float,
         * height: float,
         * x: float,
         * y: float
         * }> $stickers An array of stickers to apply.
         * @param array{
         * content: string,
         * fontFamily: string,
         * fontSize: float,
         * color: string,
         * x: float,
         * y: float
         * }|null $textOverlay An associative array containing text overlay properties.
         * @param string $filterName The name of the filter to apply (e.g., 'grayscale', 'sepia', 'vintage', 'dream', or 'none').
         * @param string $filePath The file path where the composed image will be saved.
         * @return bool Returns true on success, false on failure.
         * @throws \InvalidArgumentException If any of the sticker paths are invalid.
         * @throws \RuntimeException If the sticker image cannot be resized.
         */
    public function compose(array $stickers, ?array $textOverlay, string $filterName, string $filePath): bool {
        $this->applyFilter($filterName);
        $this->applyStickers($stickers);
        $this->applyTextOverlay($textOverlay);

        return imagejpeg($this->canvas, $filePath);
    }

    /**
     * Applies the stickers to the canvas image.
     * @param list<array{
     * path: string,
     * width: float,
     * height: float,
     * x: float,
     * y: float
     * }> $stickers An array of stickers to apply.
     * @throws \InvalidArgumentException If any of the sticker paths are invalid.
     * @throws \RuntimeException If the sticker image cannot be resized.
     */
    private function applyStickers(array $stickers): void {
        foreach ($stickers as $sticker) {
            $stickerSource = $sticker['path'] ?? null;
            if (!is_string($stickerSource) || $stickerSource === '') {
                throw new \InvalidArgumentException("Missing sticker path.");
            }

            $stickerPath = Path::join($this->publicPath, $stickerSource);
            $stickerImage = imagecreatefrompng($stickerPath);
            if (!$stickerImage) {
                throw new \InvalidArgumentException("Invalid sticker image path: " . $stickerSource);
            }

            $roundedWidth = (int) round((float) ($sticker['width'] ?? 0));
            $roundedHeight = (int) round((float) ($sticker['height'] ?? 0));

            imagealphablending($stickerImage, false);
            imagesavealpha($stickerImage, true);

            $resizedSticker = imagescale($stickerImage, $roundedWidth, $roundedHeight, IMG_BICUBIC);
            if ($resizedSticker === false) {
                throw new \RuntimeException("Failed to resize sticker image.");
            }

            imagealphablending($resizedSticker, true);
            imagesavealpha($resizedSticker, true);

            imagecopy(
                $this->canvas,
                $resizedSticker,
                (int) round((float) ($sticker['x'] ?? 0)),
                (int) round((float) ($sticker['y'] ?? 0)),
                0,
                0,
                imagesx($resizedSticker),
                imagesy($resizedSticker)
            );
        }
    }

    /**
     * Applies the text overlay to the canvas image.
     * /**
     * @param array{content: string, fontFamily: string, fontSize: float, color: string, x: float, y: float}|null $textOverlay
     * @throws \InvalidArgumentException If the font path is invalid.
     */
    private function applyTextOverlay(?array $textOverlay = null): void {
        if ($textOverlay === null) {
            return;
        }

        $fontPath = Path::join($this->fontsDirPath, FONT_MAP[$textOverlay['fontFamily']] ?? 'Raleway-Regular.ttf');
        $fontSize = $textOverlay['fontSize'] ?? 20;
        $hexColor = ltrim($textOverlay['color'] ?? '#001919', '#');

        $color = imagecolorallocate(
            $this->canvas,
            hexdec(substr($hexColor, 0, 2)),
            hexdec(substr($hexColor, 2, 2)),
            hexdec(substr($hexColor, 4, 2))
        );
        if ($color === false) {
            $color = (int)hexdec('009689'); // Fallback
        }

        $content = $textOverlay['content'] ?? '';

        // Convert the client's "top of box" y into the baseline y imagefttext() expects.
        $bbox = imagettfbbox((float)$fontSize, 0.0, $fontPath, $content);
        $ascent = -$bbox[7];

        imagefttext(
            $this->canvas,
            (float)$fontSize,
            0.0,
            (int) round((float) ($textOverlay['x'] ?? 0)),
            (int) round((float) ($textOverlay['y'] ?? 0) + $ascent),
            $color,
            $fontPath,
            $content
        );
    }

    private function applyFilter(string $filterName): void {
        switch ($filterName) {
            case 'grayscale':
                $this->applyGreyscaleFilter();
                break;
            case 'sepia':
                $this->applySepiaFilter();
                break;
            case 'vintage':
                $this->applyVintageFilter();
                break;
            case 'dream':
                $this->applyDreamFilter();
                break;
            case 'none':
            default:
                break;
        }
    }

    private function applyGreyscaleFilter(): void {
        imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
    }

    private function applySepiaFilter():void {
        imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 112, 66, 20);
    }

    private function applyVintageFilter(): void {
        $this->applyPartialGrayscale(50);
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 28, 17, 5);
        imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 13);
        imagefilter($this->canvas, IMG_FILTER_CONTRAST, -4);
    }

    private function applyDreamFilter(): void {
        imagefilter($this->canvas, IMG_FILTER_GAUSSIAN_BLUR);
        imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 51);
        imagefilter($this->canvas, IMG_FILTER_CONTRAST, -25);
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 30, -10, 10);
    }

    private function applyPartialGrayscale(int $percentage): void {
        $width = imagesx($this->canvas);
        $height = imagesy($this->canvas);

        $grayscaleCopy = imagecreatetruecolor($width, $height);
        imagecopy($grayscaleCopy, $this->canvas, 0, 0, 0, 0, $width, $height);
        imagefilter($grayscaleCopy, IMG_FILTER_GRAYSCALE);

        imagecopymerge($this->canvas, $grayscaleCopy, 0, 0, 0, 0, $width, $height, $percentage);
    }
}
