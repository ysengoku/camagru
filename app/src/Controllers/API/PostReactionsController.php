<?php

require_once __DIR__ . '/../../Views/post/comments.php';
require_once __DIR__ . '/../../helper/mailer.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PostReactionsController extends Controller {
    final public function like(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

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

    final public function removeLike(): string {
        if (Request::getMethod() !== 'DELETE') {
            return $this->methodNotAllowed();
        }

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
        $postId = Request::getMethod() === 'DELETE'
            ? Request::getQueryParam('postId')
            : (Request::getPostData()['postId'] ?? null);

        if (is_numeric($postId) === false) {
            return $this->json(['error' => 'Invalid post ID'], Response::BAD_REQUEST);
        }

        $user = User::getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'User not authenticated'], Response::UNAUTHORIZED);
        }

        $like = Like::findByUserAndPost($user->id, (int)$postId);

        return [(int)$postId, $user, $like];
    }

    final public function getComments(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

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

        $user = User::getCurrentUser();

        $comments = $limit > 0
            ? Comment::findByPostIdWithPagination((int)$postId, $offset, $limit)
            : [];
        $html = '';
        foreach ($comments as $comment) {
            $author = User::find($comment->author_id);
            $authorName = $author ? htmlspecialchars($author->username, ENT_QUOTES) : 'Unknown';
            $authorAvatar = $author ? htmlspecialchars($author->avatar, ENT_QUOTES) : null;
            $commentData = new PostCommentData(
                id: $comment->id,
                author_id: $comment->author_id,
                author_name: $authorName,
                author_avatar: $authorAvatar,
                created_at: $comment->created_at ?? '',
                content: $comment->content
            );
            $html .= render_comment($commentData, $user?->id);
        }

        return $this->json(['html' => $html, 'count' => $commentCount], Response::OK);
    }

    final public function addComment(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $data = Request::getPostData();
        $postId = $data['postId'] ?? null;
        $content = $data['content'] ?? '';

        if (is_numeric($postId) === false || empty(trim($content))) {
            return $this->json(['error' => 'Invalid post ID or empty content'], Response::BAD_REQUEST);
        }

        $user = User::getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'User not authenticated'], Response::UNAUTHORIZED);
        }

        $post = Post::find((int)$postId);
        if ($post === null) {
            return $this->json(['error' => 'Post not found'], Response::NOT_FOUND);
        }

        $comment = new Comment((int)$postId, $user->id, trim($content));
        if ($comment->save()) {
            $commentData = new PostCommentData(
                id: $comment->id,
                author_id: $user->id,
                author_name: $user->username,
                author_avatar: $user->avatar,
                created_at: $comment->created_at ?? '',
                content: $comment->content
            );

            $commentCount = Comment::countByPostId((int)$postId);

            $postAuthor = User::find($post->user_id);
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

    final public function deleteComment(): string {
        if (Request::getMethod() !== 'DELETE') {
            return $this->methodNotAllowed();
        }

        $commentId = Request::getQueryParam('commentId');
        if (is_numeric($commentId) === false) {
            return $this->json(['error' => 'Invalid comment ID'], Response::BAD_REQUEST);
        }

        $user = User::getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'User not authenticated'], Response::UNAUTHORIZED);
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
