<?php

require_once __DIR__ . '/../../helper/mailer.php';
require_once __DIR__ . '/../../helper/token.php';

final class SignupService {
    use SingletonTrait;

    /** @psalm-suppress PropertyNotSetInConstructor - always set first thing in processSignup(), before any private method that reads it runs */
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

        $verificationTokenData = generateToken(32, 15);
        $createdUser = $this->createUser($availability['existingUnverifiedUser'], $verificationTokenData);
        if ($createdUser === null) {
            return ServiceResult::failure(['general' => 'Failed to create user.']);
        }

        sendVerificationLinkEmail($createdUser->email, $verificationTokenData['token']);

        SessionStore::set(SessionKey::PendingEmail, $createdUser->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::Signup->value);

        return ServiceResult::success(['user' => $createdUser]);
    }

    private function validateSignupData(SignupData $data): array {
        $errors = [];

        $usernameError = AuthInputValidator::validateUsername($data->username);
        if ($usernameError !== null) {
            $errors['username'] = $usernameError;
        }

        $emailError = AuthInputValidator::validateEmail($data->email);
        if ($emailError !== null) {
            $errors['email'] = $emailError;
        }

        $passwordError = AuthInputValidator::validatePassword($data->password);
        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        return $errors;
    }

    /**
     * Checks if the username and email are available for registration.
     * If an unverified user exists with the same email, it will be returned for potential deletion before creating a new user.
     * @return array{errors: array<string, string>, existingUnverifiedUser: ?User}
    */
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

    /**
     * Creates a new user in the database, deleting any existing unverified user with the same email if applicable.
     * @param array{token: string, expiresAt: string} $tokenData
     */
    private function createUser(?User $existingUnverifiedUser, array $tokenData): ?User {
        $existingUnverifiedUser?->delete();

        $passwordHash = password_hash($this->userData->password, PASSWORD_DEFAULT);

        $verificationToken = $tokenData['token'];
        $verificationTokenExpiresAt = $tokenData['expiresAt'];

        $newUser = new User(
            username: $this->userData->username,
            email: $this->userData->email,
            passwordHash: $passwordHash,
            emailVerificationToken: $verificationToken,
            emailVerificationTokenExpiresAt: $verificationTokenExpiresAt
        );

        return $newUser->createNewUser() ? $newUser : null;
    }
}
