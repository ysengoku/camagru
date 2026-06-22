<?php
/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PhotoApiController extends Controller {
    final public function create(): void {
        $response = new Response();
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
        $filter = $data['filter'] ?? null;

        if (!$baseImage || empty($stickers)) {
            $response->sendApiResponse(
                ['error' => 'Missing required elements'],
                422,
                'Unprocessable Entity'
            );
            exit;
        }

        // TODO: Send the data to Model

        $responseContent = [ 
            'message' => 'Post created successfully',
            'data' => [
                'baseImage' => $baseImage,
                'stickers' => $stickers,
                'textOverlay' => $text,
                'filter' => $filter,
            ]
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
