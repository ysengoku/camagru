<?php

final class SessionService {
    use SingletonTrait;

    public function processLogin(string $username, string $password): ServiceResult {
        $user = User::findByUsername($username);
        if (!$user || !$user->isEmailVerified() || !password_verify($password, $user->password_hash)) {
            return ServiceResult::failure(['general' => 'Invalid username or password.']);
        }

        SessionStore::setUserSession($user->id);

        return ServiceResult::success();
    }

    public function processLogout(): ServiceResult {
        if (!SessionStore::activeSession()) {
            return ServiceResult::failure(['general' => 'No active session found.']);
        }

        SessionStore::clearUserSession();
        session_destroy();

        return ServiceResult::success();
    }
}
