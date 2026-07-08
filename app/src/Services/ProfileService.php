<?php

require_once __DIR__ . '/../helper/mailer.php';
require_once __DIR__ . '/../helper/token.php';

final class ProfileService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function updateProfile(ProfileData $data): ServiceResult {
        $user = User::getCurrentUser();
        if ($user === null) {
            return ServiceResult::failure(['general' => 'Not authenticated.']);
        }

        $validationErrors = $this->validateProfileData($data, $user);
        if (!empty($validationErrors)) {
            return ServiceResult::failure($validationErrors);
        }

        $user->username = $data->username;
        $user->avatar = $data->avatar;
        $user->email_notifications_enabled = $data->notificationsEnabled ? 1 : 0;
        if ($data->newPassword !== null && $data->newPassword !== '') {
            $user->password_hash = password_hash($data->newPassword, PASSWORD_DEFAULT);
        }

        if ($this->isEmailChangeRequested($data, $user)) {
            if (!$this->setupPendingEmail($user, $data)) {
                return ServiceResult::failure(['email' => 'Failed to update email.']);
            }

            return ServiceResult::success([
                'message' => 'Please verify your new email address.',
                'emailVerificationRequired' => true
            ]);
        }

        if (!$user->save()) {
            return ServiceResult::failure(['email' => 'Failed to update email.']);
        }

        return ServiceResult::success(['message' => 'Profile updated successfully.', 'emailVerificationRequired' => false]);
    }

    private function validateProfileData(ProfileData $data, User $user): array {
        $errors = [];

        $usernameError = AuthInputValidator::validateUsername($data->username);
        if ($usernameError !== null) {
            $errors['username'] = $usernameError;
        }

        $emailError = AuthInputValidator::validateEmail($data->email);
        if ($emailError !== null) {
            $errors['email'] = $emailError;
        }

        $availabilityErrors = $this->checkAvailability($data, $user);
        $errors = array_merge($errors, $availabilityErrors);

        $hasNewPassword = $data->newPassword !== null && $data->newPassword !== '';
        if ($hasNewPassword) {
            $passwordErrors = AuthInputValidator::validatePassword($data->newPassword);
            if ($passwordErrors !== null) {
                $errors['password'] = $passwordErrors;
            }
        }

        if ($this->isAvatarValid($data->avatar, $user) === false) {
            $errors['avatar'] = 'Invalid avatar selection.';
        }

        if ($this->isEmailChangeRequested($data, $user) || $hasNewPassword) {
            if ($data->password === null || $data->password === '') {
                $errors['general'] = 'Current password is required to change email or password.';
            } elseif (!$this->isCurrentPasswordValid($data->password, $user)) {
                $errors['general'] = 'Current password is incorrect.';
            }
        }

        return $errors;
    }

    private function checkAvailability(ProfileData $data, User $user): array {
        $errors = [];

        if (!$this->isUsernameAvailable($data->username, $user)) {
            $errors['username'] = 'Username is already taken.';
        }

        if (!$this->isEmailAvailable($data->email, $user)) {
            $errors['email'] = 'Email is already in use.';
        }

        return $errors;
    }

    private function isUsernameAvailable(string $username, User $user): bool {
        if ($user->username === $username) {
            return true;
        }

        if (User::findByUsername($username) !== null) {
            return false;
        }

        return true;
    }

    private function isEmailAvailable(string $email, User $user): bool {
        if ($user->email === $email) {
            return true;
        }

        if (User::findByEmail($email) !== null) {
            return false;
        }

        return true;
    }

    private function isCurrentPasswordValid(string $currentPassword, User $user): bool {
        return password_verify($currentPassword, $user->password_hash);
    }

    private function isAvatarValid(?string $avatar, User $user): bool {
        if ($avatar === null || $avatar === '') {
            return true;
        }

        $post = Post::findByPath($avatar);
        if ($post === null) {
            return false;
        }

        if ($post->user_id !== $user->id) {
            return false;
        }

        return true;
    }

    private function isEmailChangeRequested(ProfileData $data, User $user): bool {
        return $data->email !== '' && $data->email !== $user->email;
    }

    private function setupPendingEmail(User $user, ProfileData $data): bool {
        $token = generateToken(32);
        $user->pending_email = $data->email;
        $user->email_verification_token = $token['token'];
        $user->email_verification_token_expires_at = $token['expiresAt'];
        if (!$user->save()) {
            return false;
        }

        sendVerificationLinkEmail($data->email, $token['token'], false);

        SessionStore::set(SessionKey::PendingEmail, $data->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::EmailChange->value);

        return true;
    }
}
