<?php

use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase {
    // ===== join() ===========================================================
    public function testJoinProducesCorrectly():void {
        $pathSegmentOne   = 'assets';
        $pathSegmentTwo   = 'images';
        $pathSegmentThree = 'icons';
        $expected = $pathSegmentOne . '/' . $pathSegmentTwo . '/' . $pathSegmentThree;

        $path = Path::join($pathSegmentOne, $pathSegmentTwo, $pathSegmentThree);
        $this->assertSame($expected, $path);
    }

    public function testJoinProducesCorrectlyAbsolutePath():void {
        $pathSegmentOne   = '/assets';
        $pathSegmentTwo   = 'images';
        $pathSegmentThree = 'icons';
        $expected = $pathSegmentOne . '/' . $pathSegmentTwo . '/' . $pathSegmentThree;

        $path = Path::join($pathSegmentOne, $pathSegmentTwo, $pathSegmentThree);
        $this->assertSame($expected, $path);
    }

    public function testJoinWithDuplicatedSlashes():void {
        $pathSegmentOne   = 'assets/';
        $pathSegmentTwo   = '/images/';
        $pathSegmentThree = 'icons/';
        $expected = rtrim($pathSegmentOne . substr($pathSegmentTwo, 1) . $pathSegmentThree, '/');

        $path = Path::join($pathSegmentOne, $pathSegmentTwo, $pathSegmentThree);
        $this->assertSame($expected, $path);
    }

    public function testJoinWithEmptySegments():void {
        $pathSegmentOne   = 'assets';
        $pathSegmentTwo   = '';
        $pathSegmentThree = 'icons';
        $expected = $pathSegmentOne . '/' . $pathSegmentThree;

        $path = Path::join($pathSegmentOne, $pathSegmentTwo, $pathSegmentThree);
        $this->assertSame($expected, $path);
    }

    public function testJoinWithBlankSegments():void {
        $pathSegmentOne   = 'assets';
        $pathSegmentTwo   = '   ';
        $pathSegmentThree = 'icons';
        $expected = $pathSegmentOne . '/' . $pathSegmentThree;

        $path = Path::join($pathSegmentOne, $pathSegmentTwo, $pathSegmentThree);
        $this->assertSame($expected, $path);
    }

    public function testJoinWithNoSegment():void {
        $path = Path::join();
        $this->assertSame('', $path);
    }

    // ===== ensureDirectory() ================================================

    public function testEnsureDirectoryWithNonexistentDir(): void {
        $dirName = sys_get_temp_dir() . '/temp-' . uniqid();
        $result = Path::ensureDirectory($dirName);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dirName);

        rmdir($dirName);
    }

    public function testEnsureDirectoryWithExistentDir(): void {
        $dirName = sys_get_temp_dir() . '/temp-' . uniqid();
        @mkdir($dirName);
        $result = Path::ensureDirectory($dirName);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dirName);

        rmdir($dirName);
    }

    // ===== getMediaDir() ====================================================

    public function testGetMediaDirPathUsesEnvVarWhenSet(): void {
        $testMediaDir = '/tmp/custom-media';
        putenv("MEDIA_DIR={$testMediaDir}");
        $this->assertSame($testMediaDir, Path::getMediaDirPath());

        putenv('MEDIA_DIR');
    }

    public function testGetMediaDirPathFallsBackWhenEnvVarNotSet(): void {
        putenv('MEDIA_DIR');
        $result = Path::getMediaDirPath();
        $this->assertContains($result, ['/var/www/storage/media', Path::join(Path::getPublicPath(), 'media')]);
    }
}
