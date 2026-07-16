<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class FeedController extends Controller {
    public function index(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        $user = User::getCurrentUser();

        $posts = Post::allOrderedByCreationDesc();
        $postData = array_map(function (Post $post) use ($user): PostData {
            $author = User::find($post->user_id);
            if (!$author) {
                throw new RuntimeException("Author not found for post ID {$post->id}");
            }

            $is_liked_by_current_user = $user === null
                ? false
                : Like::likedByUser($user->id, $post->id);

            return new PostData(
                id: $post->id,
                author_name: $author->username,
                author_id: $author->id,
                author_avatar: $author->avatar,
                image_path: $post->image_path,
                created_at: $post->created_at ?? '',
                likes_count: Like::countByPostId($post->id),
                is_liked_by_current_user: $is_liked_by_current_user,
                comments_count: Comment::countByPostId($post->id),
                comments: [],
            );
        }, $posts);

        return $this->render([
            'pageScript' => 'feed',
            'user' => $user,
            'posts' => $postData
        ]);
    }
}
