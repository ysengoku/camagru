<?php

class FeedController extends Controller {
    public function index() {
        // $userModel = new User();
        // $currentUser = $userModel->getCurrentUser();
        // $users = $userModel->getAllUsers();

        // $postModel = new Post();
        // $posts = $postModel->getAllPosts();

        // test data
        $user = [
            'id' => 1,
            'username' => 'john_doe',
        ];

        $posts = [
            [
                'id' => 1,
                'author_name' => 'john_doe',
                'author_id' => 1,
                'image_path' => '/img/sample-pic.jpg',
                'caption' => 'A beautiful sunset!',
                'created_at' => '2024-06-01 18:30:00',
                'likes_count' => 10,
                'comments' => [
                    ['author_name' => 'jane_smith', 'text' => 'Amazing view!'],
                    ['author_name' => 'alice_wonder', 'text' => 'Love this!']
                ]
            ],
            [
                'id' => 2,
                'author_name' => 'jane_smith',
                'author_id' => 2,
                'image_path' => '/img/sample-pic2.jpg',
                'caption' => 'Had a great day at the beach.',
                'created_at' => '2024-06-02 14:15:00',
                'likes_count' => 5,
                'comments' => []
            ]
        ];

        return $this->render([
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
