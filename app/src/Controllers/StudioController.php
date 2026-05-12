<?php

class StudioController extends Controller {
    public function index() {
        // If the user is not authenticated, redirect to login page
            // header('Location: /login');
            // exit();

        // Load all overlays from the overlays directory
        $overlays = [];
        $overlayDir = __DIR__ . '/../../public/assets/overlays/';
        if (is_dir($overlayDir)) {
            $files = array_diff(scandir($overlayDir), ['.', '..']);
            foreach ($files as $file) {
                if (preg_match('/\.(png|jpg|jpeg|svg)$/i', $file)) {
                    $overlays[] = '/assets/overlays/' . $file;
                }
            }
            sort($overlays);
        }

        // Test data
        $user = [
            'id' => 1,
            'username' => 'john_doe',
            'avatar_path' => '/assets/img/sample-pic3.jpg'
        ];

        $posts = [
            [
                'id' => 1,
                'image_path' => '/assets/img/sample-pic.jpg',
                'created_at' => '2024-06-01 18:30:00',
            ],
            [
                'id' => 2,
                'image_path' => '/assets/img/sample-pic2.jpg',
                'created_at' => '2024-06-02 14:15:00',
            ],
            [
                'id' => 3,
                'image_path' => '/assets/img/sample-pic3.jpg',
                'created_at' => '2024-06-03 10:00:00',
            ]

        ];

        return $this->render([
            'user' => $user,
            'posts' => $posts,
            'overlays' => $overlays
        ]);
    }
}
