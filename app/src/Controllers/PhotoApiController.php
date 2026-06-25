<?php

require_once __DIR__ . '/../helper/ImageComposer.php';
require_once __DIR__ . '/../Models/Post.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PhotoApiController extends Controller {
    final public function create(): void {
        $response = new Response();
        $mediaDir = Path::getMediaDirPath();
        $imageFilename = uniqid('', true) . '.jpg';
        $imagePath = Path::join($mediaDir, $imageFilename);
        $publicImagePath = '/media/' . $imageFilename;

        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response->sendApiResponse(
                ['error' => 'Method Not Allowed'],
                405,
                'Method Not Allowed'
            );
            exit;
        }

        $request = new Request();
        $data = $request->getPostData();

        $baseImage = $data['baseImage'] ?? null;
        $stickers = $data['stickers'] ?? [];
        /** @var mixed $text */
        $text = $data['textOverlay'] ?? null;
        /** @var string $filter */
        $filter = $data['filter'] ?? 'none';

        if (!is_string($baseImage) || $baseImage === '' || !is_array($stickers) || empty($stickers)) {
            $response->sendApiResponse(
                ['error' => 'Missing required elements'],
                422,
                'Unprocessable Entity'
            );
            exit;
        }

        if (!Path::ensureDirectory($mediaDir) || !is_writable($mediaDir)) {
            error_log("Media directory is not writable: $mediaDir");
            $response->sendApiResponse(
                ['error' => 'Media directory is not writable'],
                500,
                'Internal Server Error'
            );
            exit;
        }

        try {
            error_log("Creating image at path: $imagePath");

            $imageComposer = new ImageComposer($baseImage);
            /**
             * @var list<array{path: string, width: float, height: float, x: float, y: float}> $stickers
             * @var array{content: string, fontFamily: string, fontSize: float, color: string, x: float, y: float}|null $text
             */
            $saved = $imageComposer->compose($stickers, $text, $filter, $imagePath);
        } catch (\Throwable $e) {
            error_log('Image creation failed: ' . $e->getMessage());
            $response->sendApiResponse(
                ['error' => 'Image creation failed'],
                500,
                'Internal Server Error'
            );
            exit;
        }

        if (!$saved) {
            error_log("Image save failed: $imagePath");
            $response->sendApiResponse(
                ['error' => 'Image save failed'],
                500,
                'Internal Server Error'
            );
            exit;
        }

        $post = new Post($publicImagePath, 1); // TODO Replace 1 with the actual user ID from session or auth context
        if (!$post->save()) {
            $response->sendApiResponse(
                ['error' => 'Post could not be saved', 'details' => $post->getErrors()],
                422,
                'Unprocessable Entity'
            );
            exit;
        }

        $responseContent = [ 
            'message' => 'Post created successfully',
            'data' => $post->toArray(),
        ];
        $response->sendApiResponse($responseContent, 201, 'Created');
        exit;
    }

    final public function delete(): void {
        $response = new Response();
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $response->sendApiResponse(
                ['error' => 'Method Not Allowed'],
                405,
                'Method Not Allowed'
            );
            exit;
        }

        // TESTING PURPOSES ONLY
        $response->sendApiResponse(['success' => true], 200, 'OK');
        exit;
    }
}
