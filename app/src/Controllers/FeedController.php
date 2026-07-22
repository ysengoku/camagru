<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class FeedController extends Controller {
    public function index(): string {
        $userId = $this->currentUser?->id ?? null;

        $posts = Post::findAllUsersPostsWithPagination();
        $postData = array_map(function (Post $post) use ($userId): PostData {
            return PostDataFactory::fromPost($post, $userId, []);
        }, $posts);

        $count = Post::countAll();

        return $this->render([
            'pageScript' => 'feed',
            'user' => $this->currentUser,
            'posts' => $postData,
            'count' => $count,
        ]);
    }
}
