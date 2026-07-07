<?php

require_once __DIR__ . '/../helper/mailer.php';
require_once __DIR__ . '/../helper/token.php';

final class ProfileService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function updateProfile(ProfileData $data): ServiceResult {
        $validationErrors = $this->validateProfileData($data);
    
        if (!empty($validationErrors)) {
            return ServiceResult::failure($validationErrors);
        }

        $user = User::getCurrentUser();
        $user->username = $data->username;
        $user->avatar = $data->avatar;
        $user->email_notifications_enabled = $data->notificationsEnabled;
        error_log("New password: {$data->newPassword}");
        if ($data->newPassword) {
            $user->password_hash = password_hash($data->newPassword, PASSWORD_DEFAULT);
        }

        if ($this->isEmailChangeRequested($data)) {
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

    private function validateProfileData(ProfileData $data): array {
        $errors = [];

        $usernameError = AuthInputValidator::validateUsername($data->username);
        if ($usernameError) {
            $errors['username'] = $usernameError;
        }

        $emailError = AuthInputValidator::validateEmail($data->email);
        if ($emailError) {
            $errors['email'] = $emailError;
        }

        $availabilityErrors = $this->checkAvailability($data);
        $errors = array_merge($errors, $availabilityErrors);

        if ($data->newPassword) {
            $passwordErrors = AuthInputValidator::validatePassword($data->newPassword);
            if ($passwordErrors) {
                $errors['password'] = $passwordErrors;
            }
        }  

        if ($this->isAvatarValid($data->avatar) === false) {
            $errors['avatar'] = 'Invalid avatar selection.';
        }

        if ($this->isEmailChangeRequested($data) || !empty($data->newPassword)) {
            if (empty($data->password)) {
                $errors['general'] = 'Current password is required to change email or password.';
            } elseif (!$this->isCurrentPasswordValid($data->password)) {
                $errors['general'] = 'Current password is incorrect.';
            }
        }

        return $errors;
    }

    private function checkAvailability(ProfileData $data): array {
        $errors = [];

        if (!$this->isUsernameAvailable($data->username)) {
            $errors['username'] = 'Username is already taken.';
        }

        if (!$this->isEmailAvailable($data->email)) {
            $errors['email'] = 'Email is already in use.';
        }

        return $errors;
    }

    private function isUsernameAvailable(string $username): bool {
        if (User::getCurrentUser()->username === $username) {
            return true;
        }

        if (User::findByUsername($username) !== null) {
            return false;
        }

        return true;
    }

    private function isEmailAvailable(string $email): bool {
        if (User::getCurrentUser()->email === $email) {
            return true;
        }

        if (User::findByEmail($email) !== null) {
            return false;
        }

        return true;
    }

    private function isCurrentPasswordValid(string $currentPassword): bool {
        $currentUser = User::getCurrentUser();
        return password_verify($currentPassword, $currentUser->password_hash);
    }

    private function isAvatarValid(?string $avatar): bool {
        if ($avatar === null || $avatar === '') {
            return true;
        }

        $post = Post::findByPath($avatar);
        if ($post === null) {
            return false;
        }

        if ($post->user_id !== User::getCurrentUser()->id) {
            return false;
        }

        return true;
    }

    private function isEmailChangeRequested(ProfileData $data): bool {
        return !empty($data->email) && $data->email !== User::getCurrentUser()->email;
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
