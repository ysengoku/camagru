<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class ProfileController extends Controller {
    public function index(): string {
        return $this->render([
            'pageScript' => 'profile',
            'pageTitle' => 'Profile'
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
}
