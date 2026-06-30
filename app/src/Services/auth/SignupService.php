<?php

final class SignupService {
    private static ?SignupService $instance = null;
    private SignupData $userData;
    private array $rules;

    private function __construct() {
        // Private constructor to prevent direct instantiation
        $this->rules = require __DIR__ . '/../../config/validation.php';
    }

    public static function getInstance(): SignupService {
        if (self::$instance === null) {
            self::$instance = new SignupService();
        }
        return self::$instance; 
    }

    public function processSignup(SignupData $data): array {
        $this->userData = $data;
        $validationErrors = $this->validateSignupData($data);

        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }

        $availability = $this->checkAvailability();
        if (!empty($availability['errors'])) {
            return ['success' => false, 'errors' => $availability['errors']];
        }

        $createdUser = $this->createUser($availability['existingUnverifiedUser']);
        if ($createdUser === null) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create user.']];
        }

        // $this->sendVerificationEmail($createdUser->email, $createdUser->verification_token);

        return ['success' => true, 'user' => $createdUser];
    }

    private function validateSignupData(SignupData $data): array {
        $errors = [];

        $usernameError = $this->validateUsername($data->username);
        if ($usernameError) {
            $errors['username'] = $usernameError;
        }

        $emailError = $this->validateEmail($data->email);
        if ($emailError) {
            $errors['email'] = $emailError;
        }

        $passwordError = $this->validatePassword($data->password);
        if ($passwordError) {
            $errors['password'] = $passwordError;
        }

        return $errors;
    }

    private function validateUsername(string $username): ?string {
        if (empty($username)) {
            return $this->rules['username']['messages']['required'];
        }
        if (strlen($username) < $this->rules['username']['minLength'] || strlen($username) > $this->rules['username']['maxLength']) {
            return 'Username must be between ' . $this->rules['username']['minLength'] . ' and ' . $this->rules['username']['maxLength'] . ' characters long.';
        }
        if (!preg_match('/' . $this->rules['username']['pattern'] . '/', $username)) {
            return $this->rules['username']['messages']['pattern'];
        }
        return null;
    }

    private function validateEmail(string $email): ?string {
        if (empty($email)) {
            return $this->rules['email']['messages']['required'];
        }
        if (!preg_match('/' . $this->rules['email']['pattern'] . '/', $email)) {
            return $this->rules['email']['messages']['pattern'];
        }
        if (strlen($email) > $this->rules['email']['maxLength']) {
            return 'Email must not exceed ' . $this->rules['email']['maxLength'] . ' characters.';
        }
        return null;
    }

    private function validatePassword(string $password): ?string {
        if (empty($password)) {
            return $this->rules['password']['messages']['required'];
        }
        if (strlen($password) < $this->rules['password']['minLength'] || strlen($password) > $this->rules['password']['maxLength']) {
            return 'Password must be between ' . $this->rules['password']['minLength'] . ' and ' . $this->rules['password']['maxLength'] . ' characters long.';
        }
        if ($this->rules['password']['requireLower'] && !preg_match('/[a-z]/', $password)
            || ($this->rules['password']['requireUpper'] && !preg_match('/[A-Z]/', $password))
            || ($this->rules['password']['requireDigit'] && !preg_match('/\d/', $password))
            || !preg_match('/' . $this->rules['password']['specialCharPattern'] . '/', $password)) {
            return $this->rules['password']['messages']['pattern'];
        }
        return null;
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
        $verificationToken = bin2hex(random_bytes(32));
        $verificationTokenExpiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

        $newUser = new User($this->userData->username, $this->userData->email, $passwordHash, $verificationToken, $verificationTokenExpiresAt);

        return $newUser->createNewUser() ? $newUser : null;
    }

    private function sendVerificationEmail(string $email, string $token) {
        $verificationLink = "https://{$_SERVER['HTTP_HOST']}/verify-email?token={$token}";
        $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
        $subject = "Verify Your Email Address";
        $body = renderEmailTemplate('verification', ['logoUrl' => $logoUrl, 'verificationLink' => $verificationLink]);

        try {
            EmailService::getInstance()->send($email, $subject, $body);
        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
        }
    }
}
