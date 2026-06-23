<?php
const FONT_MAP = [
    'Raleway' => 'Raleway-Regular.ttf',
    'Bebas Neue' => 'BebasNeue-Regular.ttf',
    'HomemadeApple' => 'HomemadeApple-Regular.ttf',
    'Lexend' => 'Lexend-VariableFont_wght.ttf',
    'Pacifico' => 'Pacifico-Regular.ttf',
    'Playfair Display' => 'PlayfairDisplay-VariableFont_wght.ttf'
];

class ImageComposer {
    private \GdImage $canvas;
    private int $width;
    private int $height;

    private string $publicPath;
    private string $fontsDirPath;

    public function __construct(string $base64Image) {
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/i', '', $base64Image));
        $this->canvas = imagecreatefromstring($imageData);

        if (!$this->canvas) {
            throw new \InvalidArgumentException("Invalid base64 image data.");
        }

        $this->width = imagesx($this->canvas);
        $this->height = imagesy($this->canvas);

        $this->publicPath = realpath(Path::getPublicPath());
        $this->fontsDirPath = Path::join($this->publicPath, 'assets', 'fonts');
    }

    public function compose(array $stickers, ?array $textOverlay, string $filterName, string $filePath): bool {
        $this->applyFilter($filterName);
        $this->applyStickers($stickers);
        $this->applyTextOverlay($textOverlay);

        return imagejpeg($this->canvas, $filePath);
    }

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

            $resizedSticker = imagescale($stickerImage, $roundedWidth, $roundedHeight);

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

    private function applyTextOverlay(?array $textOverlay): void {
        if (empty($textOverlay)) {
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

        imagefttext(
            $this->canvas,
            (float)$fontSize,
            0.0,
            (int) round((float) ($textOverlay['x'] ?? 0)),
            (int) round((float) ($textOverlay['y'] ?? 0)),
            $color,
            $fontPath,
            $textOverlay['content'] ?? ''
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
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 56, 33, 10);
        imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 13);
        imagefilter($this->canvas, IMG_FILTER_CONTRAST, -4);
        imagefilter($this->canvas, IMG_FILTER_GRAYSCALE);
    }

    private function applyDreamFilter(): void {
        imagefilter($this->canvas, IMG_FILTER_GAUSSIAN_BLUR);
        imagefilter($this->canvas, IMG_FILTER_BRIGHTNESS, 51);
        imagefilter($this->canvas, IMG_FILTER_CONTRAST, -25);
        imagefilter($this->canvas, IMG_FILTER_COLORIZE, 30, -10, 10);
    }
}
