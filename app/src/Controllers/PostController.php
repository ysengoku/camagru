<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class PostController extends Controller {
    public function view(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        $user = User::getCurrentUser();
        $isOverlay = Request::isXmlHttpRequest() === true;

        $postId = Request::getQueryParam('postId');
        if (is_numeric($postId) === false) {
            error_log("Invalid postId parameter: " . htmlspecialchars((string)$postId));
            throw new HTTPNotFoundException();
        }

        $post = Post::find((int)$postId);
        if ($post === null) {
            error_log("Post not found for postId: " . htmlspecialchars((string)$postId));
            throw new HTTPNotFoundException();
        }

        $comments = Comment::findByPostIdWithPagination($post->id, 0, 10);
        $commentsData = array_map(function (Comment $comment): PostCommentData {
            return PostDataFactory::toCommentData($comment);
        }, $comments);

        $postData = PostDataFactory::fromPost($post, $user?->id ?? null, $commentsData);

        $params = [
            'pageTitle' => 'Post View',
            'user' => $user,
            'postData' => $postData,
            'isOverlay' => $isOverlay,
        ];

        return $isOverlay 
            ? $this->renderContent($params, 'view')
            : $this->render($params, 'view');
    }
}
