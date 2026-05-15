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
            'avatar_path' => '/assets/img/sample-pic3.jpg'
        ];

        $posts = [
            [
                'id' => 1,
                'author_name' => 'john_doe',
                'author_id' => 1,
                'author_avatar' => null,
                'image_path' => '/assets/img/sample-pic.jpg',
                'created_at' => '2024-06-01 18:30:00',
                'likes_count' => 10,
                'comments_count' => 2,
                'comments' => [
                    ['author_name' => 'jane_smith', 'text' => 'Amazing view!'],
                    ['author_name' => 'alice_wonder', 'text' => 'Love this!']
                ]
            ],
            [
                'id' => 2,
                'author_name' => 'jane_smith',
                'author_id' => 2,
                'author_avatar' => '/assets/img/sample-pic2.jpg',
                'image_path' => '/assets/img/sample-pic2.jpg',
                'created_at' => '2024-06-02 14:15:00',
                'likes_count' => 5,
                'comments_count' => 0,
                'comments' => []
            ],
            [
                'id' => 3,
                'author_name' => 'alice_wonder',
                'author_id' => 3,
                'author_avatar' => '/assets/img/sample-pic3.jpg',
                'image_path' => '/assets/img/sample-pic3.jpg',
                'created_at' => '2024-06-03 10:00:00',
                'likes_count' => 20,
                'comments_count' => 3,
                'comments' => [
                    ['author_name' => 'john_doe', 'text' => 'Yummy!'],
                    ['author_name' => 'jane_smith', 'text' => 'Recipe, please!'],
                    ['author_name' => 'bob_builder', 'text' => 'Looks great!']
                ]
            ]

        ];

        return $this->render([
            'pageScript' => 'feed',
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
