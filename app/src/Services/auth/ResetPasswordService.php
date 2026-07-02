<?php

final class ResetPasswordService {
    use SingletonTrait;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public function validateToken(string $token): ServiceResult {
        if (empty($token)) {
            error_log("Token is required for password reset.");

            return ServiceResult::failure(['token' => 'Token is required.']);
        }

        $user = User::findByPasswordResetToken($token);
        if ($user === null) {
            error_log("No user found for the provided password reset token.");

            return ServiceResult::failure(['token' => 'Invalid token.']);
        }

        $expiresAt = $user->password_reset_token_expires_at;
        if ($expiresAt === null || strtotime($expiresAt) < time()) {
            error_log("Password reset token has expired for user id {$user->id}.");

            return ServiceResult::failure(['token' => 'Token has expired.']);
        }

        return ServiceResult::success();
    }

    public function processResetPassword(string $token, string $newPassword): ServiceResult {
        $tokenValidation = $this->validateToken($token);
        if (!$tokenValidation->success) {
            return ServiceResult::failure($tokenValidation->errors);
        }

        if (AuthInputValidator::validatePassword($newPassword)) {
            return ServiceResult::failure(['password' => 'Invalid password format.']);
        }

        $user = User::findByPasswordResetToken($token);
        if ($user === null) {
            return ServiceResult::failure(['token' => 'Invalid token.']);
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->password_hash = $newPasswordHash;
        $user->password_reset_token = null;
        $user->password_reset_token_expires_at = null;
        $user->save();

        return ServiceResult::success();
    }
}
