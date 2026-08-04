<?php

require_once __DIR__ . '/../../Views/post/comments.php';
require_once __DIR__ . '/../../helper/mailer.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PostReactionsController extends Controller {
    /**
     * Adds a like from the current user to a post.
     *
     * @route POST /api/like
     * @bodyParam int postId
     * @response 201 {message, likesCount} Like added
     * @response 400 {error} Invalid post ID, or already liked
     */
    final public function like(): string {
        $context = $this->resolveLikeContext();
        if (is_string($context)) {
            return $context;
        }
        [$postId, $user, $like] = $context;

        if ($like !== null) {
            return $this->json(['error' => 'The user has already liked this post'], Response::BAD_REQUEST);
        }

        $newLike = new Like($user->id, $postId);
        if ($newLike->save()) {
            $likesCount = Like::countByPostId($postId);

            return $this->json([
                    'message' => 'Like added',
                    'likesCount' => $likesCount
                ], Response::CREATED);
        }

        return $this->json(['error' => 'Failed to add like'], Response::INTERNAL_ERROR);
    }

    /**
     * Removes the current user's like from a post.
     *
     * @route DELETE /api/like
     * @queryParam int postId
     * @response 200 {message, likesCount} Like removed
     * @response 400 {error} Invalid post ID, or not liked
     */
    final public function removeLike(): string {
        $context = $this->resolveLikeContext();
        if (is_string($context)) {
            return $context;
        }
        [$postId, , $like] = $context;

        if ($like === null) {
            return $this->json(['error' => 'The user has not liked this post. Cannot remove like.'], Response::BAD_REQUEST);
        }

        if ($like->delete()) {
            $likesCount = Like::countByPostId($postId);
    
            return $this->json([
                'message' => 'Like removed',
                'likesCount' => $likesCount
            ], Response::OK);
        }

        return $this->json(['error' => 'Failed to remove like'], Response::INTERNAL_ERROR);
    }

    /**
     * Resolves the postId, current user, and any existing Like shared by like()/removeLike().
     * @return array{0: int, 1: User, 2: ?Like}|string A JSON error response string on failure.
     */
    private function resolveLikeContext(): array|string {
        $user = $this->getAuthenticatedUser();
        $postId = Request::getMethod() === 'DELETE'
            ? Request::getQueryParam('postId')
            : (Request::getPostData()['postId'] ?? null);

        if (is_numeric($postId) === false) {
            return $this->json(['error' => 'Invalid post ID'], Response::BAD_REQUEST);
        }

        $like = Like::findByUserAndPost($user->id, (int)$postId);

        return [(int)$postId, $user, $like];
    }

    /**
     * Returns a paginated HTML fragment of a post's comments.
     *
     * @route GET /api/comments
     * @queryParam int postId
     * @queryParam int offset Defaults to 0
     * @queryParam int limit Defaults to 10, max 50
     * @response 200 {html, count}
     * @response 400 {error} Invalid post ID, or invalid offset/limit
     */
    final public function getComments(): string {
        $postId = Request::getQueryParam('postId');
        if (is_numeric($postId) === false) {
            return $this->json(['error' => 'Invalid post ID'], Response::BAD_REQUEST);
        }

        $commentCount = Comment::countByPostId((int)$postId);
        $offset = (int)(Request::getQueryParam('offset') ?? 0);
        $limit = (int)(Request::getQueryParam('limit') ?? 10);

        if ($offset < 0 || $limit <= 0 || $limit > 50) {
            return $this->json(['error' => 'Invalid offset or limit'], Response::BAD_REQUEST);
        }

        $limit = min($limit, max($commentCount - $offset, 0));

        // $user = User::getCurrentUser();

        $comments = $limit > 0
            ? Comment::findByPostIdWithPagination((int)$postId, $offset, $limit)
            : [];
        $html = '';
        foreach ($comments as $comment) {
            $commentData = PostDataFactory::toCommentData($comment);
            $html .= render_comment($commentData, $this->currentUser?->id);
        }

        return $this->json(['html' => $html, 'count' => $commentCount], Response::OK);
    }

    /**
     * Adds a comment to a post and notifies the post's author by email.
     *
     * @route POST /api/comments
     * @bodyParam int postId
     * @bodyParam string content
     * @response 201 {message, html, postId, commentCount} Comment added
     * @response 400 {error} Invalid post ID or empty content
     * @response 404 {error} Post not found
     */
    final public function addComment(): string {
        $data = Request::getPostData();
        $postId = $data['postId'] ?? null;
        $content = $data['content'] ?? '';
        $user = $this->getAuthenticatedUser();

        if (is_numeric($postId) === false || empty(trim($content))) {
            return $this->json(['error' => 'Invalid post ID or empty content'], Response::BAD_REQUEST);
        }

        $post = Post::find((int)$postId);
        if ($post === null) {
            return $this->json(['error' => 'Post not found'], Response::NOT_FOUND);
        }

        $comment = new Comment((int)$postId, $user->id, trim($content));
        if ($comment->save()) {
            $commentData = PostDataFactory::toCommentData($comment);
            $commentCount = Comment::countByPostId((int)$postId);

            $postAuthor = $post->user() ?? null;
            if ($postAuthor !== null && $postAuthor->id !== $user->id) {
                $this->sendNotification($postAuthor, $user->username, trim($content), (int)$postId);
            }

            return $this->json([
                'message' => 'Comment added',
                'html' => render_comment($commentData, $user->id),
                'postId' => (int)$postId,
                'commentCount' => $commentCount
            ], Response::CREATED);
        }

        return $this->json(['error' => 'Failed to add comment'], Response::INTERNAL_ERROR);
    }

    /**
     * Deletes a comment owned by the current user.
     *
     * @route DELETE /api/comments
     * @queryParam int commentId
     * @response 200 {message, postId, commentCount} Comment deleted
     * @response 400 {error} Invalid comment ID
     * @response 403 {error} Comment not found, or user not authorized to delete it
     */
    final public function deleteComment(): string {
        $user = $this->getAuthenticatedUser();
        $commentId = Request::getQueryParam('commentId');
        if (is_numeric($commentId) === false) {
            return $this->json(['error' => 'Invalid comment ID'], Response::BAD_REQUEST);
        }

        $comment = Comment::find((int)$commentId);
        if ($comment === null || $comment->author_id !== $user->id) {
            return $this->json(['error' => 'Comment not found or user not authorized to delete it'], Response::FORBIDDEN);
        }

        if ($comment->delete()) {
            $commentCount = Comment::countByPostId($comment->post_id);
            return $this->json([
                'message' => 'Comment deleted',
                'postId' => $comment->post_id,
                'commentCount' => $commentCount
            ], Response::OK);
        }
        
        return $this->json(['error' => 'Failed to delete comment'], Response::INTERNAL_ERROR);
    }

    private function sendNotification(User $postAuthor, string $commentAuthorName, string $content, int $postId): void {
        if (!$postAuthor->email_notifications_enabled) {
            return;
        }

        sendNewCommentNotificationEmail(
            $postAuthor->email,
            $commentAuthorName,
            $content,
            $postId
        );
    }
}
