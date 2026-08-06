<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ImageComposerTest extends TestCase {
    private static string $validBase64Image;
    private static string $stickerPath;
    private static string $stickerFullPath;

    public static function setUpBeforeClass(): void {
        $canvas = imagecreatetruecolor(10, 10);
        imagefilledrectangle($canvas, 0, 0, 9, 9, imagecolorallocate($canvas, 255, 0, 0));
        ob_start();
        imagejpeg($canvas);
        $imageData = ob_get_clean();
        self::$validBase64Image = 'data:image/jpeg;base64,' . base64_encode($imageData);

        self::$stickerPath = '/assets/stickers/test-sticker.png';
        self::$stickerFullPath = Path::join(realpath(Path::getPublicPath()), self::$stickerPath);
        if (!is_dir(dirname(self::$stickerFullPath))) {
            mkdir(dirname(self::$stickerFullPath), 0777, true);
        }
        $sticker = imagecreatetruecolor(5, 5);
        imagepng($sticker, self::$stickerFullPath);
    }

    public static function tearDownAfterClass():void {
        @unlink(self::$stickerFullPath);
    }

    public function testConstructorRejectInvalidBase():void {
        $this->expectException(\InvalidArgumentException::class);
        @new ImageComposer('not-a-real-image');
    }

    public function testControllerAcceptValidImage():void {
        $composer = new ImageComposer(self::$validBase64Image);
        $this->assertInstanceOf(ImageComposer::class, $composer);
    }

    public static function filterProvider(): array {
        return [
            'none'      => ['none'],
            'grayscale' => ['grayscale'],
            'sepia'     => ['sepia'],
            'vintage'   => ['vintage'],
            'polaroid'  => ['polaroid'],
        ];
    }

    #[DataProvider('filterProvider')]
    public function testComposeWithEachFilter(string $filter) {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $result = $composer->compose([], null, $filter, $outputPath);
        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($outputPath);
    }

    public function testComposeWithUnknownFilterAppliesNoFilter():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $result = $composer->compose([], null, 'unknown', $outputPath);
        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($outputPath);
    }

    public function testComposeRejectStickerWithMissingPath():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $this->expectException(\InvalidArgumentException::class);
        $sticker = ['width' => 10, 'height' => 10, 'x' => 0, 'y' => 0];
        $composer->compose([$sticker], null, 'none', $outputPath);
    }

    public function testComposeRejectInvalidStickerPath():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $this->expectException(\InvalidArgumentException::class);
        $sticker = ['path' => '/assets/stickers/nonexistent.png', 'width' => 5, 'height' => 5, 'x' => 0, 'y' => 0];
        @$composer->compose([$sticker], null, 'none', $outputPath);
    }

    public function testComposeRejectWithValidStickerSize():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $this->expectException(\RuntimeException::class);
        $sticker = ['path' => self::$stickerPath, 'width' => 0, 'height' => 0, 'x' => 0, 'y' => 0];
         $composer->compose([$sticker], null, 'none', $outputPath);
    }

    public function testComposeWithValidSticker():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $sticker = ['path' => self::$stickerPath, 'width' => 5, 'height' => 5, 'x' => 0, 'y' => 0];
        $result  = $composer->compose([$sticker], null, 'none', $outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($outputPath);
    }

    public function testComposeWithMultipleValidStickers():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $stickerTwoPath = '/assets/stickers/test-sticker2.png';
        $stickerTwoFullPath = Path::join(realpath(Path::getPublicPath()), $stickerTwoPath);
        $stickerTwo = imagecreatetruecolor(5, 5);
        imagepng($stickerTwo, $stickerTwoFullPath);

        $stickerData    = ['path' => self::$stickerPath, 'width' => 5, 'height' => 5, 'x' => 0, 'y' => 0];
        $stickerTwoData =  ['path' => $stickerTwoPath, 'width' => 3, 'height' => 3, 'x' => 6, 'y' => 6];
        $result         = $composer->compose([$stickerData, $stickerTwoData], null, 'none', $outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($stickerTwoFullPath);
        unlink($outputPath);
    }

    public function testComposeUnknownTextFontFallbacksToDefault():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $text   = [
            'content'=> 'Test Content',
            'fontFamily' => 'Unknown',
            'fontSize' => 16,
            'color' => 'ffffff',
            'x' => 0,
            'y' => 0
        ];
        $result = $composer->compose([], $text, 'none', $outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($outputPath);
    }

    public function testComposeWithValidTextOverlay():void {
        $composer   = new ImageComposer(self::$validBase64Image);
        $outputPath = sys_get_temp_dir() . '/composer-test-' . uniqid() . '.jpg';
 
        $text   = [
            'content'=> 'Test Content',
            'fontFamily' => 'Raleway',
            'fontSize' => 16,
            'color' => 'ffffff',
            'x' => 0,
            'y' => 0
        ];
        $result = $composer->compose([], $text, 'none', $outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($outputPath);
        $this->assertNotFalse(getimagesize($outputPath));

        unlink($outputPath);
    }
}
