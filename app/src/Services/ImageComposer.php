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
    // GD's TrueType size parameter is effectively points at 96dpi, not px;
    // browsers render CSS px directly. Convert with the standard 72/96 ratio.
    private const float GD_FONT_SIZE_CORRECTION = 72.0 / 96.0;

    private \GdImage $canvas;

    private string $publicPath;
    private string $fontsDirPath;

    /**
     * @param string $imagePath Path to the uploaded image file.
     * @throws \InvalidArgumentException If the image file cannot be read or is invalid.
     * @throws \RuntimeException If the public path cannot be resolved.
     */
    public function __construct(string $imagePath) {
        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new \InvalidArgumentException("Invalid image data.");
        }

        $canvas = imagecreatefromstring($imageData);
        if ($canvas === false) {
            throw new \InvalidArgumentException("Invalid image data.");
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
         * @param string $filterName The name of the filter to apply (e.g., 'grayscale', 'sepia', 'vintage', 'polaroid', or 'none').
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

            try {
                $resizedSticker = imagescale($stickerImage, $roundedWidth, $roundedHeight, IMG_BICUBIC);
            } catch (\ValueError $e) {
                throw new \RuntimeException("Failed to resize sticker image.");
            }
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
     * @param array{content: string, fontFamily: string, fontSize: float, color: string, x: float, y: float}|null $textOverlay
     * @throws \InvalidArgumentException If the font path is invalid.
     */
    private function applyTextOverlay(?array $textOverlay = null): void {
        if ($textOverlay === null) {
            return;
        }

        $fontPath = Path::join($this->fontsDirPath, FONT_MAP[$textOverlay['fontFamily']] ?? 'Raleway-Regular.ttf');
        $cssFontSize = $textOverlay['fontSize'] ?? 20.0;
        /** @psalm-suppress InvalidOperand - both operands are float; Psalm misreads the const's literal division */
        $fontSize = $cssFontSize * self::GD_FONT_SIZE_CORRECTION;
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

        $bbox = imagettfbbox($fontSize, 0.0, $fontPath, $content);
        if ($bbox === false) {
            throw new \RuntimeException("Failed to calculate text bounding box.");
        }

        $canvasWidth = imagesx($this->canvas);
        $canvasHeight = imagesy($this->canvas);

        $x = (int) round((float) ($textOverlay['x'] ?? 0));
        $x = max(-$bbox[0], min($x, $canvasWidth - $bbox[2]));

        // Converts the client's "top of box" y into the baseline y imagefttext() expects.
        [$lineAscent, $lineDescent] = $this->getLineMetrics($fontPath, $cssFontSize);

        $baselineY = (float) ($textOverlay['y'] ?? 0) + $lineAscent;
        $baselineY = (int) round(max($lineAscent, min($baselineY, (float) $canvasHeight - $lineDescent)));

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
    }

    /**
     * Reads the font's design-space ascender/descender from its 'hhea' table and
     * scales them to pixels at the given size, matching how browsers derive
     * line-height from font metrics (independent of GD_FONT_SIZE_CORRECTION).
     * @return array{0: float, 1: float} [ascentPx, descentPx]
     * @throws \RuntimeException If the font file or its metrics tables can't be read.
     */
    private function getLineMetrics(string $fontPath, float $fontSize): array {
        $data = file_get_contents($fontPath);
        if ($data === false) {
            throw new \RuntimeException("Failed to read font file: " . $fontPath);
        }

        // Byte 4-5 of a TTF file is the table count; a directory of 16-byte
        // entries (4-byte tag + offset) follows, starting at byte 12.
        $numTablesData = unpack('n', substr($data, 4, 2));
        if ($numTablesData === false) {
            throw new \RuntimeException("Failed to parse font file: " . $fontPath);
        }

        $tableOffsets = [];
        for ($i = 0; $i < $numTablesData[1]; $i++) {
            $entry = substr($data, 12 + $i * 16, 16);
            $offsetData = unpack('N', substr($entry, 8, 4));
            if ($offsetData === false) {
                throw new \RuntimeException("Failed to parse font file: " . $fontPath);
            }
            $tableOffsets[substr($entry, 0, 4)] = $offsetData[1];
        }

        if (!isset($tableOffsets['head']) || !isset($tableOffsets['hhea'])) {
            throw new \RuntimeException("Font file is missing required metrics tables: " . $fontPath);
        }

        // unitsPerEm lives in 'head'; Ascender/Descender live in 'hhea', both
        // at fixed byte offsets within their table.
        $unitsPerEmData = unpack('n', substr($data, $tableOffsets['head'] + 18, 2));
        $ascenderData = unpack('n', substr($data, $tableOffsets['hhea'] + 4, 2));
        $descenderData = unpack('n', substr($data, $tableOffsets['hhea'] + 6, 2));
        if ($unitsPerEmData === false || $ascenderData === false || $descenderData === false) {
            throw new \RuntimeException("Failed to parse font metrics: " . $fontPath);
        }

        // unpack('n', ...) reads unsigned, but Ascender/Descender can be negative.
        $unitsPerEm = (float) $unitsPerEmData[1];
        $ascender = (float) self::toSignedInt16($ascenderData[1]);
        $descender = (float) self::toSignedInt16($descenderData[1]);

        // Scale from the font's own units (relative to unitsPerEm) to pixels.
        return [
            $ascender * $fontSize / $unitsPerEm,
            -$descender * $fontSize / $unitsPerEm,
        ];
    }

    private static function toSignedInt16(int $value): int {
        return $value > 32767 ? $value - 65536 : $value;
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
            case 'polaroid':
                $this->applyPolaroidFilter();
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

    private function applyPolaroidFilter(): void {
        $this->applyPartialGrayscale(20);
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 11, 7, 2);
        imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 32);
        imagefilter($this->canvas, IMG_FILTER_CONTRAST, -25);
    }

    private function applyPartialGrayscale(int $percentage): void {
        $width = imagesx($this->canvas);
        $height = imagesy($this->canvas);

        $grayscaleCopy = imagecreatetruecolor($width, $height);
        if ($grayscaleCopy === false) {
            throw new \RuntimeException("Failed to create grayscale copy of the image.");
        }
        imagecopy($grayscaleCopy, $this->canvas, 0, 0, 0, 0, $width, $height);
        imagefilter($grayscaleCopy, IMG_FILTER_GRAYSCALE);

        imagecopymerge($this->canvas, $grayscaleCopy, 0, 0, 0, 0, $width, $height, $percentage);
    }
}
