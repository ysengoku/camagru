<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PhotoApiController extends Controller {
    final public function create(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $mediaDir = Path::getMediaDirPath();
        $imageFilename = uniqid('', true) . '.jpg';
        $imagePath = Path::join($mediaDir, $imageFilename);
        $publicImagePath = '/media/' . $imageFilename;

        $data = Request::getPostData();

        $baseImage = $data['baseImage'] ?? null;
        $stickers  = $data['stickers'] ?? [];
        /** @var mixed $text */
        $text   = $data['textOverlay'] ?? null;
        /** @var string $filter */
        $filter = $data['filter'] ?? 'none';

        if (!is_string($baseImage) || $baseImage === '' || !is_array($stickers) || empty($stickers)) {
            return $this->json(['error' => 'Missing required elements'], Response::UNPROCESSABLE);
        }

        if (!Path::ensureDirectory($mediaDir) || !is_writable($mediaDir)) {
            error_log("Media directory is not writable: $mediaDir");
            return $this->json(['error' => 'Media directory is not writable'], Response::INTERNAL_ERROR);
        }

        try {
            $imageComposer = new ImageComposer($baseImage);
            /**
             * @var list<array{path: string, width: float, height: float, x: float, y: float}> $stickers
             * @var array{content: string, fontFamily: string, fontSize: float, color: string, x: float, y: float}|null $text
             */
            $saved = $imageComposer->compose($stickers, $text, $filter, $imagePath);
        } catch (\Throwable $e) {
            error_log('Image creation failed: ' . $e->getMessage());
            return $this->json(['error' => 'Image creation failed'], Response::INTERNAL_ERROR);
        }

        if (!$saved) {
            error_log("Image save failed: $imagePath");
            return $this->json(['error' => 'Image save failed'], Response::INTERNAL_ERROR);
        }

        $post = new Post($publicImagePath, 1); // TODO: replace 1 with session user ID
        if (!$post->save()) {
            return $this->json(
                ['error' => 'Post could not be saved', 'details' => $post->getErrors()],
                Response::UNPROCESSABLE
            );
        }

        return $this->json(['message' => 'Post created successfully', 'data' => $post->toArray()], Response::CREATED);
    }

    final public function delete(): string {
        // TESTING PURPOSES ONLY
        return $this->json(['success' => true], Response::OK);
    }
}
