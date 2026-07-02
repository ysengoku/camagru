<?php

final class LoginService {
    use SingletonTrait;

    public function processLogin(string $username, string $password): ServiceResult {
        $user = User::findByUsername($username);
        if (!$user) {
            return ServiceResult::failure(['general' => 'Invalid username.']);
        }

        if (!$user->isEmailVerified()) {
            return ServiceResult::failure(['general' => 'Please verify your email before logging in.']);
        }

        if (!password_verify($password, $user->password_hash)) {
            return ServiceResult::failure(['general' => 'Invalid username or password.']);
        }

        SessionStore::setUserSession($user->id);

        return ServiceResult::success();
    }
}
