<?php
/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */

require_once __DIR__ . '/../helper/ImageComposer.php';

final class PhotoApiController extends Controller {
    final public function create(): void {
        $response = new Response();
        $mediaDir = Path::getMediaDirPath();
        $imageFilename = uniqid('', true) . '.jpg';
        $imagePath = Path::join($mediaDir, $imageFilename);
        $publicImagePath = '/media/' . $imageFilename;

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
        $text = $data['textOverlay'] ?? null;
        $filter = $data['filter'] ?? 'none';

        if (!$baseImage || empty($stickers)) {
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
            $saved = $imageComposer->compose($stickers, $text ?? [], $filter, $imagePath);
        } catch (\Throwable $e) {
            error_log('Photo creation failed: ' . $e->getMessage());
            $response->sendApiResponse(
                ['error' => 'Photo creation failed'],
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

        // TODO: Save the image path to the database via Model

        $responseContent = [ 
            'message' => 'Post created successfully',
            'data' => ['path' => $publicImagePath],
        ];
        $response->sendApiResponse($responseContent, 201, 'Created');
        exit;
    }

    final public function delete(): void {
        $response = new Response();
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
