<?php

require_once __DIR__ . '/../../helper/mailer.php';

final class SignupService {
    use SingletonTrait;

    private SignupData $userData;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function processSignup(SignupData $data): ServiceResult {
        $this->userData = $data;
        $validationErrors = $this->validateSignupData($data);
    
        if (!empty($validationErrors)) {
            return ServiceResult::failure($validationErrors);
        }

        $availability = $this->checkAvailability();
        if (!empty($availability['errors'])) {
            return ServiceResult::failure($availability['errors']);
        }

        $createdUser = $this->createUser($availability['existingUnverifiedUser']);
        if ($createdUser === null) {
            return ServiceResult::failure(['general' => 'Failed to create user.']);
        }

        // $this->sendVerificationEmail($createdUser->email, $createdUser->email_verification_token);
        sendVerificationLinkEmail($createdUser->email, $createdUser->email_verification_token);

        SessionStore::set(SessionKey::PendingEmail, $createdUser->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::Signup->value);

        return ServiceResult::success(['user' => $createdUser]);
    }

    private function validateSignupData(SignupData $data): array {
        $errors = [];

        $usernameError = AuthInputValidator::validateUsername($data->username);
        if ($usernameError) {
            $errors['username'] = $usernameError;
        }

        $emailError = AuthInputValidator::validateEmail($data->email);
        if ($emailError) {
            $errors['email'] = $emailError;
        }

        $passwordError = AuthInputValidator::validatePassword($data->password);
        if ($passwordError) {
            $errors['password'] = $passwordError;
        }

        return $errors;
    }

    private function checkAvailability(): array {
        $errors = [];

        $existingByEmail = User::findByEmail($this->userData->email);
        $isRetry = $existingByEmail !== null && !$existingByEmail->email_verified;

        $existingByUsername = User::findByUsername($this->userData->username);
        if ($existingByUsername !== null) {
            $isSameUnverifiedRetry = $existingByUsername->email === $this->userData->email
                && !$existingByUsername->email_verified;

            if (!$isSameUnverifiedRetry) {
                $errors['username'] = 'Username is already taken.';
            }
        }

        if ($existingByEmail !== null && $existingByEmail->email_verified) {
            $errors['email'] = 'Email is already registered.';
        }

        return [
            'errors' => $errors,
            'existingUnverifiedUser' => $isRetry ? $existingByEmail : null,
        ];
    }

    private function createUser(?User $existingUnverifiedUser): ?User {
        $existingUnverifiedUser?->delete();

        $passwordHash = password_hash($this->userData->password, PASSWORD_DEFAULT);
        
        $verificationTokenData = generateToken(32);
        $verificationToken = $verificationTokenData['token'];
        $verificationTokenExpiresAt = $verificationTokenData['expiresAt'];

        $newUser = new User(
            username: $this->userData->username,
            email: $this->userData->email,
            passwordHash: $passwordHash,
            emailVerificationToken: $verificationToken,
            emailVerificationTokenExpiresAt: $verificationTokenExpiresAt
        );

        return $newUser->createNewUser() ? $newUser : null;
    }

    // public function sendVerificationEmail(string $email, string $token): void {
    //     $verificationLink = getenv('APP_BASE_URL') . "/verify-email?token={$token}";
    //     $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
    //     $subject = "Verify Your Email Address";
    //     $body = renderEmailTemplate('verification', ['logoUrl' => $logoUrl, 'verificationLink' => $verificationLink]);

    //     try {
    //         // EmailService::getInstance()->send($email, $subject, $body);
    //         SessionStore::set(SessionKey::LastEmailSentTime, time());
    //     } catch (Exception $e) {
    //         error_log("Failed to send verification email: " . $e->getMessage());
    //     }
    // }
}
