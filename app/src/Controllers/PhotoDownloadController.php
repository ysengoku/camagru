<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PhotoDownloadController extends Controller {
    final public function downloadPhoto(): string {
        $user = $this->getAuthenticatedUser();
        $postId = Request::getQueryParam('postId');
        if (!is_string($postId) || $postId === '') {
            return $this->json(['error' => 'Invalid post ID'], Response::BAD_REQUEST);
        }

        $post = Post::find((int) $postId);
        if ($post === null) {
            return $this->json(['error' => 'Post not found'], Response::NOT_FOUND);
        }
        if ($post->user_id !== $user->id) {
            return $this->json(['error' => 'Unauthorized to download this post'], Response::FORBIDDEN);
        }

        // basename() strips any directory segments, so a tampered image_path can't
        // be used for path traversal outside the media directory.
        $filePath = Path::join(Path::getMediaDirPath(), basename($post->image_path));
        if (!is_readable($filePath)) {
            return $this->json(['error' => 'File not found'], Response::NOT_FOUND);
        }

        // Clear any output buffers to prevent corruption of the file download
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Mime type detection using finfo
        $detectedMimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
        $mimeType = $detectedMimeType === false ? 'application/octet-stream' : $detectedMimeType;
        header('Content-Type: ' . $mimeType);

        $filesize = filesize($filePath);
        if ($filesize !== false) {
            header('Content-Length: ' . $filesize);
        }

        header('Content-Disposition: attachment; filename="camagru-photo-' . $post->id . '.jpg"');
        header('Cache-Control: no-cache');
            
        // Read the file and send it to the output buffer
        readfile($filePath);
        exit;
    }
}
