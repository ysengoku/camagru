<?php

require_once __DIR__ . '/../../helper/mailer.php';
require_once __DIR__ . '/../../helper/token.php';

final class ForgotPasswordService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function processForgotPassword(string $email): ServiceResult {
        $validationErrors = AuthInputValidator::validateEmail($email);

        if ($validationErrors !== null) {
            return ServiceResult::failure(['email' => $validationErrors, 'status' => Response::BAD_REQUEST]);
        }

        $secondsUntilEmailSendAllowed = SessionStore::secondsUntilEmailSendAllowed();
        if ($secondsUntilEmailSendAllowed > 0) {
            return ServiceResult::failure([
                'general'        => 'Please wait before requesting another password reset email.',
                'time_remaining' => $secondsUntilEmailSendAllowed,
                'status'         => Response::TOO_MANY_REQUESTS
            ]);
        }

        // Always report success regardless of whether the account exists or is
        // verified, so this endpoint can't be used to enumerate registered emails.
        $user = User::findByEmail($email);
        if ($user !== null && $user->isEmailVerified()) {
            $token = $this->issueResetToken($user);
            sendPasswordResetEmail($email, $token);
        } else {
            error_log("Password reset requested for non-existent or unverified email: $email");
        }

        SessionStore::set(SessionKey::PendingEmail, $email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::ResetPassword->value);
        SessionStore::set(SessionKey::LastEmailSentTime, time());

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

        return $resetToken;
    }
}
