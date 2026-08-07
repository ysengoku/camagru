<?php

final class Path {
    private static string $ds = DIRECTORY_SEPARATOR;

    public static function getPublicPath(): string {
        return self::join(__DIR__, '..', '..', 'public');
    }

    public static function getMediaDirPath(): string {
        $mediaDir = getenv('MEDIA_DIR');
        if (is_string($mediaDir) && $mediaDir !== '') {
            return $mediaDir;
        }

        if (is_dir('/var/www') || is_dir('/var/www/storage')) {
            return '/var/www/storage/media';
        }

        return self::join(self::getPublicPath(), 'media');
    }

    public static function ensureDirectory(string $path): bool {
        return is_dir($path) || mkdir($path, 0755, true);
    }

    public static function join(string ...$segments): string {
        if (empty($segments)) {
            return '';
        }

        $cleanedSegments = array_map(function ($segment) {
            return trim($segment, " \t\n\r\0\x0B\\/");
        }, $segments);

        $filteredSegments = array_filter($cleanedSegments, function ($segment) {
            return $segment !== '';
        });

        $isAbsolutePath = str_starts_with($segments[0], '/') || str_starts_with($segments[0], '\\');
        $joined = implode(self::$ds, $filteredSegments);

        return $isAbsolutePath ? self::$ds . $joined : $joined;
    }
}
