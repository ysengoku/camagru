<?php

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
            
        $offset = (int)(Request::getQueryParam('offset') ?? 0);
        $limit = (int)(Request::getQueryParam('limit') ?? 5);

        $comments = Comment::findByPostIdWithPagination((int)$postId, $offset, $limit);
        return $this->json([
            'comments' => array_map(fn(Comment $comment): array => $comment->toArray(), $comments)],
            Response::OK
        );
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

        $comment = new Comment((int)$postId, $user->id, trim($content));
        if ($comment->save()) {
            return $this->json(['message' => 'Comment added', 'comment' => $comment->toArray()], Response::CREATED);
        } 
        return $this->json(['error' => 'Failed to add comment'], Response::INTERNAL_ERROR);
    }
}
