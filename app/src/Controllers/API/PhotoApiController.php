<?php

require_once __DIR__ . '/../../Views/feed/postPreview.php';
require_once __DIR__ . '/../../Views/studio/galleryItem.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PhotoApiController extends Controller {
    /**
     * Composes a base image with stickers/text/filter and saves the result as a new post.
     *
     * @route POST /api/photos
     * @bodyParam string $baseImage Base64-encoded source image
     * @bodyParam array $stickers List of {path, width, height, x, y}
     * @bodyParam array $textOverlay {content, fontFamily, fontSize, color, x, y}, optional
     * @bodyParam string $filter Filter name, defaults to "none"
     * @response 201 {message, html} Post created successfully
     * @response 422 {error} Missing required elements, or post could not be saved
     * @response 500 {error} Media directory not writable, or image creation/save failed
     */
    final public function create(): string {
        $user = $this->getAuthenticatedUser();
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

        $post = new Post($publicImagePath, $user->id);
        if (!$post->save()) {
            return $this->json(
                ['error' => 'Post could not be saved', 'details' => $post->getErrors()],
                Response::UNPROCESSABLE
            );
        }

        $html = render_gallery_item((string) $post->id, $post->image_path);
        return $this->json([
            'message' => 'Post created successfully',
            'html' => $html
        ], Response::CREATED);
    }

    /**
     * Deletes a post owned by the current user.
     *
     * @route DELETE /api/photos
     * @queryParam string postId
     * @response 200 {success} Post deleted
     * @response 400 {error} Invalid post ID
     * @response 403 {error} Unauthorized to delete this post
     * @response 404 {error} Post not found
     * @response 500 {error} Failed to delete post
     */
    final public function delete(): string {
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
            return $this->json(['error' => 'Unauthorized to delete this post'], Response::FORBIDDEN);
        }

        if (!$post->delete()) {
            return $this->json(['error' => 'Failed to delete post'], Response::INTERNAL_ERROR);
        }

        return $this->json(['success' => true], Response::OK);
    }

    /**
     * Returns a paginated HTML fragment of all users' posts (public feed).
     *
     * @route GET /api/photos
     * @queryParam int offset Defaults to 0
     * @queryParam int limit Defaults to 10, max 50
     * @response 200 {html, count}
     * @response 400 {error} Invalid offset or limit
     */
    final public function getPhotos(): string {
        $offset = (int)(Request::getQueryParam('offset') ?? 0);
        $limit = (int)(Request::getQueryParam('limit') ?? 10);

        if ($offset < 0 || $limit <= 0 || $limit > 50) {
            return $this->json(['error' => 'Invalid offset or limit'], Response::BAD_REQUEST);
        }

        $totalPostCount = Post::countAll();
        $limit = min($limit, max($totalPostCount - $offset, 0));

        $posts = $limit > 0 
            ? Post::findAllUsersPostsWithPagination($offset, $limit)
            : [];
        $html = '';
        foreach ($posts as $post) {
            $postData = PostDataFactory::fromPost($post, $this->currentUser?->id);            
            $html .= render_post_preview($postData);
        }

        return $this->json(['html' => $html, 'count' => $totalPostCount], Response::OK);
        
    }

    /**
     * Returns a paginated HTML fragment of the current user's own posts.
     *
     * @route GET /api/photos/me
     * @queryParam int offset Defaults to 0
     * @queryParam int limit Defaults to 10, max 50
     * @response 200 {html, count}
     * @response 400 {error} Invalid offset or limit
     */
    final public function getCurrentUserPhotos(): string {
        $offset = (int)(Request::getQueryParam('offset') ?? 0);
        $limit = (int)(Request::getQueryParam('limit') ?? 10);

        if ($offset < 0 || $limit <= 0 || $limit > 50) {
            return $this->json(['error' => 'Invalid offset or limit'], Response::BAD_REQUEST);
        }

        $user = $this->getAuthenticatedUser();
        $totalCount = Post::countByUserId($user->id);
        $limit = min($limit, max($totalCount - $offset, 0));
        $posts = $limit > 0
            ? Post::findByUserIdWithPagination($user->id, $offset, $limit)
            : [];
        $html = '';
        foreach ($posts as $post) {
            $html .= render_gallery_item((string) $post->id, $post->image_path);
        }

        return $this->json(['html' => $html, 'count' => $totalCount], Response::OK);
    }
}
