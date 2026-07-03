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

    private function deleteAllSessionsForUser(int $userId): void {
        $db = Database::getInstance();
        $sql = "DELETE FROM sessions WHERE user_id = :user_id";
        $db->execute($sql, ['user_id' => $userId]);
    }

    public function processResetPassword(string $token, string $newPassword): ServiceResult {
        $tokenValidation = $this->validateToken($token);
        if (!$tokenValidation->success) {
            return ServiceResult::failure($tokenValidation->errors);
        }

        $passwordFormatError = AuthInputValidator::validatePassword($newPassword);
        if ($passwordFormatError) {
            return ServiceResult::failure(['password' => $passwordFormatError]);
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

        $this->deleteAllSessionsForUser($user->id);

        return ServiceResult::success();
    }
}
