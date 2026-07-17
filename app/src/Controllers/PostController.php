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
        $isFullPageRendering = Request::isXmlHttpRequest() === false;

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

        $author = User::find($post->user_id);
        if ($author === null) {
            throw new RuntimeException("Author not found for post ID {$post->id}");
        }

        $is_liked_by_current_user = false;
        if ($user !== null) {
            $is_liked_by_current_user = Like::likedByUser($user->id, $post->id);
        }

        $comments = Comment::findByPostIdWithPagination($post->id, 0, 10);
        $commentsData = array_map(function (Comment $comment): PostCommentData {
            $author = User::find($comment->author_id);
            if ($author === null) {
                throw new RuntimeException("Author not found for comment ID {$comment->id}");
            }
            return new PostCommentData(
                id: $comment->id,
                author_id: $author->id,
                author_name: $author->username,
                author_avatar: $author->avatar,
                created_at: $comment->created_at ?? '',
                content: $comment->content
            );
        }, $comments);

        $postData = new PostData(
            id: $post->id,
            author_name: $author->username,
            author_id: $author->id,
            author_avatar: $author->avatar,
            image_path: $post->image_path,
            created_at: $post->created_at ?? '',
            likes_count: Like::countByPostId($post->id),
            is_liked_by_current_user: $is_liked_by_current_user,
            comments_count: Comment::countByPostId($post->id),
            comments: $commentsData,
        );

        $params = [
            'pageTitle' => 'Post View',
            'user' => $user,
            'postData' => $postData,
        ];

        return $isFullPageRendering 
            ? $this->render($params, 'view')
            : $this->renderContent($params, 'view');
    }
}
