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
        $userId = $user?->id ?? null;

        $posts = Post::findAllUsersPostsWithPagination();
        $postData = array_map(function (Post $post) use ($userId): PostData {
            return PostDataFactory::fromPost($post, $userId, []);
        }, $posts);

        $count = Post::countAll();

        return $this->render([
            'pageScript' => 'feed',
            'user' => $user,
            'posts' => $postData,
            'count' => $count,
        ]);
    }
}
