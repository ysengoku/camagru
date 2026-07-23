<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class StudioController extends Controller {
    public function index(): string {
        // Load all stickers from the stickers directory
        $stickers   = [];
        $stickerDir = __DIR__ . '/../../public/assets/stickers/';
        if (is_dir($stickerDir)) {
            $scanned = scandir($stickerDir);
            $files   = $scanned !== false ? array_diff($scanned, ['.', '..']) : [];
            foreach ($files as $file) {
                if (!preg_match('/\.(png|jpg|jpeg)$/i', $file)) {
                    continue;
                }

                $filePath   = $stickerDir . $file;
                $dimensions = @getimagesize($filePath);
                if ($dimensions === false || ($dimensions[0] * $dimensions[1]) > 6_000_000) {
                    continue;
                }

                $stickers[] = '/assets/stickers/' . $file;
            }
            sort($stickers);
        }

        $user      = $this->getAuthenticatedUser();
        $posts     = Post::findByUserIdWithpagination($user->id);
        $postCount = Post::countByUserId($user->id);
        $postData  = array_map(function (Post $post) {
            return [
                'id' => $post->id,
                'image_path' => $post->image_path,
                'created_at' => $post->created_at,
            ];
        }, $posts);

        return $this->render([
            'pageScript' => 'studio',
            'user' => $user,
            'posts' => $postData,
            'postCount' => $postCount,
            'stickers' => $stickers
        ]);
    }
}
