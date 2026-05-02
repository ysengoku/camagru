<?php

class FeedController extends Controller
{
    public function index()
    {
        $userModel = new User();
        $currentUser = $userModel->getCurrentUser();
        $users = $userModel->getAllUsers();

        $postModel = new Post();
        $posts = $postModel->getAllPosts();

        return $this->render([
            'users' => $users,
            'posts' => $posts
        ]);
    }
}
