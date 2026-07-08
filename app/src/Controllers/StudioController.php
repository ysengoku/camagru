<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class StudioController extends Controller {
    public function index(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        $user = User::getCurrentUser();
        if (!$user) {
            Response::redirect('/login');
        }

        // Load all stickers from the stickers directory
        $stickers = [];
        $stickerDir = __DIR__ . '/../../public/assets/stickers/';
        if (is_dir($stickerDir)) {
            $scanned = scandir($stickerDir);
            $files = $scanned !== false ? array_diff($scanned, ['.', '..']) : [];
            foreach ($files as $file) {
                if (preg_match('/\.(png|jpg|jpeg|svg)$/i', $file)) {
                    $stickers[] = '/assets/stickers/' . $file;
                }
            }
            sort($stickers);
        }

        $posts = Post::findByUserId($user->id);
        $postData = array_map(function (Post $post) {
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
            'stickers' => $stickers
        ]);
    }
}
