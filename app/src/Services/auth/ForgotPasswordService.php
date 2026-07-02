<?php

final class ForgotPasswordService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function processForgotPassword(string $email): ServiceResult {
        $validationErrors = AuthInputValidator::validateEmail($email);

        if (!empty($validationErrors)) {
            return ServiceResult::failure(['email' => $validationErrors]);
        }

        // Always report success regardless of whether the account exists or is
        // verified, so this endpoint can't be used to enumerate registered emails.
        $user = User::findByEmail($email);
        if ($user !== null && $user->isEmailVerified()) {
            $token = $this->issueResetToken($user);
            $this->sendPasswordResetEmail($email, $token);
        }

        return ServiceResult::success();
    }

    private function issueResetToken(User $user): string {
        $tokenData = generateToken(32, 15);
        $resetToken = $tokenData['token'];
        $resetTokenExpiresAt = $tokenData['expiresAt'];

        $user->password_reset_token = $resetToken;
        $user->password_reset_token_expires_at = $resetTokenExpiresAt;
        if (!$user->save()) {
            error_log("Failed to save password reset token for user id {$user->id}");
            return '';
        }

        SessionStore::set(SessionKey::PendingEmail, $user->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::ResetPassword->value);

        return $resetToken;
    }

    public function sendPasswordResetEmail(string $email, string $token): void {
        $resetLink = "https://{$_SERVER['HTTP_HOST']}/reset-password?token=$token";
        $subject = "Password Reset";
        $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
        $body = renderEmailTemplate('forgotPassword', ['logoUrl' => $logoUrl, 'resetLink' => $resetLink]);

        // EmailService::getInstance()->send($email, $subject, $body);
        SessionStore::set(SessionKey::LastEmailSentTime, time());
    }
}
