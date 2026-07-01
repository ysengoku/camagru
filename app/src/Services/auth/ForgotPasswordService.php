<?php

final class ForgotPasswordService {
    private static ?ForgotPasswordService $instance = null;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public static function getInstance(): ForgotPasswordService {
        if (self::$instance === null) {
            self::$instance = new ForgotPasswordService();
        }
        return self::$instance; 
    }

    public function processForgotPassword(string $email): array {
        $validationErrors = AuthInputValidator::validateEmail($email);

        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }

        $user = User::findByEmail($email);
        if ($user === null || !$user->isEmailVerified()) {
            return ['success' => false, 'errors' => ['User not found or email not verified.']];
        }

        $tokenData = generateToken(32);
        $resetToken = $tokenData['token'];
        $resetTokenExpiresAt = $tokenData['expiresAt'];

        $user->password_reset_token = $resetToken;
        $user->password_reset_token_expires_at = $resetTokenExpiresAt;
        if (!$user->save()) {
            return ['success' => false, 'errors' => ['Failed to generate password reset token.']];
        }

        // Send password reset email
        $this->sendPasswordResetEmail($user->email, $resetToken);

        return ['success' => true];
    }

    private function sendPasswordResetEmail(string $email, string $token): void {
        $resetLink = "https://{$_SERVER['HTTP_HOST']}/reset-password?token=$token";
        $subject = "Password Reset";
        $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
        $body = renderEmailTemplate('forgotPassword', ['logoUrl' => $logoUrl, 'resetLink' => $resetLink]);

        EmailService::getInstance()->send($email, $subject, $body);
    }

}
