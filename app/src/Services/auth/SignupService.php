<?php

final class SignupService {
    private static ?SignupService $instance = null;
    private SignupData $userData;
    private array $validationRules;

    private function __construct() {
        // Private constructor to prevent direct instantiation
        $this->validationRules = require __DIR__ . '/../../config/validation.php';
    }

    public static function getInstance(): SignupService {
        if (self::$instance === null) {
            self::$instance = new SignupService();
        }
        return self::$instance; 
    }

    public function processSignup(SignupData $data): array {
        $this->userData = $data;
        // $validationErrors = $this->validateSignupData($data);

        // if (!empty($validationErrors)) {
        //     return ['success' => false, 'errors' => $validationErrors];
        // }

        $availabilityErrors = $this->checkAvailability();
        if (!empty($availabilityErrors)) {
            return ['success' => false, 'errors' => $availabilityErrors];
        }

        $createdUser = $this->createUser();

        if ($createdUser === null) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create user.']];
        }

        // $this->sendVerificationEmail($createdUser->email, $createdUser->verification_token);

        return ['success' => true, 'user' => $createdUser];
    }

    private function validateSignupData(SignupData $data): array {
        $errors = [];

        if (empty($this->userData->username)) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($this->userData->username) < $this->validationRules['username']['minLength']
            || strlen($this->userData->username) > $this->validationRules['username']['maxLength']) {
            $errors['username'] = 'Username must be between ' . $this->validationRules['username']['minLength'] . ' and ' . $this->validationRules['username']['maxLength'] . ' characters long.';
        } elseif (!preg_match('/' . $this->validationRules['username']['pattern'] . '/', $this->userData->username)) {
            $errors['username'] = 'Username must contain only letters, numbers, and underscores.';
        }

        if (empty($this->userData->email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($this->userData->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        } elseif (strlen($this->userData->email) > $this->validationRules['email']['maxLength']) {
            $errors['email'] = 'Email must not exceed ' . $this->validationRules['email']['maxLength'] . ' characters.';
        }

        if (empty($this->userData->password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($this->userData->password) < $this->validationRules['password']['minLength'] || strlen($this->userData->password) > $this->validationRules['password']['maxLength']) {
            $errors['password'] = 'Password must be between ' . $this->validationRules['password']['minLength'] . ' and ' . $this->validationRules['password']['maxLength'] . ' characters long.';
        } elseif ($this->validationRules['password']['requireLower'] && !preg_match('/[a-z]/', $this->userData->password)
            || ($this->validationRules['password']['requireUpper'] && !preg_match('/[A-Z]/', $this->userData->password))
            || ($this->validationRules['password']['requireDigit'] && !preg_match('/\d/', $this->userData->password))
            || !preg_match('/' . $this->validationRules['password']['specialCharPattern'] . '/', $this->userData->password)) {
            $errors['password'] = 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character.';
        }

        return $errors;
    }

    private function checkAvailability(): array {
        $errors = [];

        if (User::findByUsername($this->userData->username)) {
            $errors['username'] = 'Username is already taken.';
        }

        if (User::findByEmail($this->userData->email)) {
            $errors['email'] = 'Email is already registered.';
        }

        return $errors;
    }

    private function createUser(): ?User {
        $passwordHash = password_hash($this->userData->password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));

        $newUser = new User($this->userData->username, $this->userData->email, $passwordHash, $verificationToken);

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
