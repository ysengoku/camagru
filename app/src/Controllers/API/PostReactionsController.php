<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PostReactionsController extends Controller {
    final public function toggleLike(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $data = Request::getPostData();
        $postId = $data['postId'] ?? null;

        if (is_numeric($postId) === false) {
            return $this->json(['error' => 'Invalid post ID'], Response::BAD_REQUEST);
        }

        $user = User::getCurrentUser();
        if ($user === null) {
            return $this->json(['error' => 'User not authenticated'], Response::UNAUTHORIZED);
        }
        $like = Like::findByUserAndPost($user->id, (int)$postId);

        if ($like !== null) {
            if ($like->delete()) {
                return $this->json(['message' => 'Like removed'], Response::OK);
            } 
            return $this->json(['error' => 'Failed to remove like'], Response::INTERNAL_ERROR);
        }

        $newLike = new Like($user->id, (int)$postId);
        if ($newLike->save()) {
            return $this->json(['message' => 'Like added'], Response::CREATED);
        } 
        return $this->json(['error' => 'Failed to add like'], Response::INTERNAL_ERROR);    
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
