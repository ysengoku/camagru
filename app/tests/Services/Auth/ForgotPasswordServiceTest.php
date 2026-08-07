<?php

final class ForgotPasswordServiceTest extends DbTestCase {
    public function testProcessForgotPasswordSucceeds(): void {
        $username = 'testuser';
        $email    = 'testuser@example.com';
        SignupService::getInstance()->processSignup(new SignupData($username, $email, 'Valid-Password123!'));
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();

        SessionStore::delete(SessionKey::LastEmailSentTime);
        $result = ForgotPasswordService::getInstance()->processForgotPassword($email);

        $this->assertTrue($result->success);
        $updatedUser = User::findByUsername($username);
        $this->assertNotNull($updatedUser->password_reset_token);
    }

    public function testProcessForgotPasswordRejectEmptyEmail(): void {
        SessionStore::delete(SessionKey::LastEmailSentTime);
        $result = ForgotPasswordService::getInstance()->processForgotPassword('');

        $this->assertFalse($result->success);
    }

    public function testProcessForgotPasswordRejectInvalidEmail(): void {
        SessionStore::delete(SessionKey::LastEmailSentTime);
        $result = ForgotPasswordService::getInstance()->processForgotPassword('test.example.com');

        $this->assertFalse($result->success);
    }

    public function testProcessForgotPasswordUnknownUser(): void {
        $email = 'unknown@example.com';
        SessionStore::delete(SessionKey::LastEmailSentTime);
        $result = ForgotPasswordService::getInstance()->processForgotPassword($email);

        $this->assertTrue($result->success);
        $unknownUser = User::findByEmail($email);
        $this->assertNull($unknownUser);
    }

    public function testProcessForgotPasswordUnverifiedUser(): void {
        $username = 'unverified';
        $email    = 'unverified@example.com';
        SignupService::getInstance()->processSignup(new SignupData($username, $email, 'Valid-Password123!'));
        SessionStore::delete(SessionKey::LastEmailSentTime);
        $result = ForgotPasswordService::getInstance()->processForgotPassword($email);

        $this->assertTrue($result->success);
        $user = User::findByUsername($username);
        $this->assertNull($user->password_reset_token);
    }

    public function testProcessForgotPasswordRejectRateLimit(): void {
        $username = 'ratelimit';
        $email    = 'ratelimit@example.com';
        SignupService::getInstance()->processSignup(new SignupData($username, $email, 'Valid-Password123!'));
        SessionStore::delete(SessionKey::LastEmailSentTime);
        ForgotPasswordService::getInstance()->processForgotPassword($email);
        $result = ForgotPasswordService::getInstance()->processForgotPassword($email);

        $this->assertFalse($result->success);
    }
}
