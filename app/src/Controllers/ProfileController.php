<?php

require_once __DIR__ . '/../Views/profile/avatarSelection.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class ProfileController extends Controller {
    public function index(): string {
        $user = $this->currentUser;
        $posts = Post::findByUserIdWithPagination($user->id, 0, 4);
        $postCount = Post::countByUserId($user->id);

        return $this->render([
            'pageScript' => 'profile',
            'pageTitle' => 'Profile',
            'posts' => $posts,
            'postCount' => $postCount,
        ]);
    }

    public function update(): string {
        $data = Request::getPostData();
        $profileData = new ProfileData(
            username: $data['username'] ?? '',
            email: $data['email'] ?? '',
            password: $data['current-password'] ?? null,
            newPassword: $data['password'] ?? null,
            avatar: $data['avatar'] ?? null,
            notificationsEnabled: isset($data['notifications']) ? (bool)$data['notifications'] : false
            );

        $res = ProfileService::getInstance()->updateProfile($profileData);
        if (!$res->success) {
            return $this->json(['error' => $res->errors], Response::BAD_REQUEST);
        }

        return $this->json($res->data, Response::OK);
    }

    public function getAvatarOptions(): string {
        $pageNumber = (int)(Request::getQueryParam('page') ?? 1);
        $limit = $pageNumber === 1 ? 4 : 5;
        $offset = $pageNumber === 1 ? 0 : 4 + ($pageNumber - 2) * 5;

        $userId = $this->currentUser->id;
        $posts = Post::findByUserIdWithPagination($userId, $offset, $limit);
        $count = Post::countByUserId($userId);
        $totalPages = $count <= 4 ? 1 : 1 + (int)ceil(($count - 4) / 5);

        $html =  render_avatar_selection_list($pageNumber, $totalPages, $this->currentUser->username, $this->currentUser->avatar, $posts);

        return $this->json([
            'html' => $html,
            'page' => $pageNumber,
            'totalPages' => $totalPages
        ], Response::OK);
    }
}
