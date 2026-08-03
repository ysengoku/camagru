<?php

require_once __DIR__ . '/../Views/profile/avatarSelection.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class ProfileController extends Controller {
    public function index(): string {
        $user = $this->getAuthenticatedUser();
        $posts = Post::findByUserIdWithPagination($user->id, 0, 4);
        $postCount = Post::countByUserId($user->id);

        return $this->render([
            'pageScript' => 'profile',
            'pageTitle' => 'Profile',
            'user' => $user,
            'posts' => $posts,
            'postCount' => $postCount,
        ]);
    }

    /**
     * Updates the current user's profile: username, email, password, avatar, and notification preference.
     *
     * @route POST /api/profile
     * @bodyParam string $username
     * @bodyParam string $email
     * @bodyParam string current-password Required when changing email or password
     * @bodyParam string $password New password
     * @bodyParam string $avatar
     * @bodyParam bool $notifications
     * @response 200 {message, emailVerificationRequired, avatarHtml} Profile updated successfully
     * @response 400 {error} Validation failed
     */
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
        $user = $this->getAuthenticatedUser();

        $res = ProfileService::getInstance()->updateProfile($profileData, $user);
        if (!$res->success) {
            return $this->json(['error' => $res->errors], Response::BAD_REQUEST);
        }

        return $this->json($res->data, Response::OK);
    }

    /**
     * Returns a paginated HTML fragment of the current user's posts,
     * used for the avatar-selection picker in profile settings.
     * 
     * @route GET /api/avatar-options
     * @queryParam int page Page number, default to 1
     * @response 200 {html, page, totalPages}
     */
    public function getAvatarOptions(): string {
        $pageNumber = (int)(Request::getQueryParam('page') ?? 1);
        $limit = $pageNumber === 1 ? 4 : 5;
        $offset = $pageNumber === 1 ? 0 : 4 + ($pageNumber - 2) * 5;

        $user = $this->getAuthenticatedUser();
        $posts = Post::findByUserIdWithPagination($user->id, $offset, $limit);
        $count = Post::countByUserId($user->id);
        $totalPages = $count <= 4 ? 1 : 1 + (int)ceil(($count - 4) / 5);

        $html =  render_avatar_selection_list($pageNumber, $totalPages, $user->username, $user->avatar, $posts);

        return $this->json([
            'html' => $html,
            'page' => $pageNumber,
            'totalPages' => $totalPages
        ], Response::OK);
    }
}
