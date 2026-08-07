<?php

final class ResetPasswordServiceTest extends DbTestCase {
    private function generatePasswordResetToken(string $username): string {
        $email    = "{$username}@example.com";
        $password = 'Valid-Password123!';
        $data     = new SignupData($username, $email, $password);
        SignupService::getInstance()->processSignup($data);

        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();

        SessionStore::delete(SessionKey::LastEmailSentTime);
        ForgotPasswordService::getInstance()->processForgotPassword($email);
        $updatedUser = User::findByUsername($username);

        return $updatedUser->password_reset_token;
    }

    // ===== validateToken() ===================================================

    public function testValidateTokenSucceeds(): void {
        $token  = $this->generatePasswordResetToken('resetpassword');
        $result = ResetPasswordService::getInstance()->validateToken($token);

        $this->assertTrue($result->success);
    }

    public function testValidateTokenRejectEmptyToken(): void {
        $result = ResetPasswordService::getInstance()->validateToken('');

        $this->assertFalse($result->success);
    }

    public function testValidateTokenRejectInvalidToken(): void {
        $token        = $this->generatePasswordResetToken('invalidtoken');
        $invalidToken = $token . 'invalid';

        $result = ResetPasswordService::getInstance()->validateToken($invalidToken);

        $this->assertFalse($result->success);
    }

    public function testValidateTokenRejectExpiredToken(): void {
        $username = 'expiredtoken';
        $token    = $this->generatePasswordResetToken($username);
        $user     = User::findByPasswordResetToken($token);
        $user->password_reset_token_expires_at = (new DateTime('-1 day'))->format('Y-m-d H:i:s');
        $user->save();

        $result = ResetPasswordService::getInstance()->validateToken($token);

        $this->assertFalse($result->success);
    }

    // ===== processResetPassword() ============================================

    public function testProcessResetPasswordSucceeds(): void {
        $token        = $this->generatePasswordResetToken('invalidtoken');
        $newPassword  = 'New_Valid-Password123!';

        $result = ResetPasswordService::getInstance()->processResetPassword($token, $newPassword);

        $this->assertTrue($result->success);
    }

    public function testProcessResetPasswordRejectInvalidToken(): void {
        $token        = $this->generatePasswordResetToken('invalidtoken');
        $invalidToken = $token . 'invalid';
        $newPassword  = 'New_Valid-Password123!';

        $result = ResetPasswordService::getInstance()->processResetPassword($invalidToken, $newPassword);

        $this->assertFalse($result->success);
    }

    public function testProcessResetPasswordRejectInvalidPasswordFormat(): void {
        $token            = $this->generatePasswordResetToken('invalidtoken');
        $invalidPassword  = 'invalid-password123!';

        $result = ResetPasswordService::getInstance()->processResetPassword($token, $invalidPassword);

        $this->assertFalse($result->success);
    }

    public function testProcessResetPasswordDeletesUserSessions(): void {
        $token = $this->generatePasswordResetToken('sessiontest');
        $user  = User::findByPasswordResetToken($token);

        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO sessions (user_id, session_token, expired_at, data) VALUES (:user_id, :session_token, :expired_at, :data)", [
                'user_id'       => $user->id,
                'session_token' => str_repeat('a', 64),
                'expired_at'    => (new DateTime('+1 day'))->format('Y-m-d H:i:s'),
                'data'          => '',
            ]
        );

        ResetPasswordService::getInstance()->processResetPassword($token, 'New-Password123!');

        $remaining = $db->fetchAll("SELECT * FROM sessions WHERE user_id = :user_id", ['user_id' => $user->id]);
        $this->assertCount(0, $remaining);
    }
}
