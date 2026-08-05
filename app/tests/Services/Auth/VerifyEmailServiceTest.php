<?php

final class VerifyEmailServiceTest extends DbTestCase {
    private function newUserToken($username): string {
        $password = 'Valid-Password123!';
        $data = new SignupData($username, "{$username}@example.com", $password);
        $result = SignupService::getInstance()->processSignup($data);

        return $result->data['user']->email_verification_token;
    }

    public function testVerifyEmailSucceeds():void {
        $username = 'invalidtoken';
        $token = $this->newUserToken($username);
        $result = VerifyEmailService::getInstance()->processVerification($token);

        $this->assertTrue($result->success);
        $user = User::findByUsername($username);
        $this->assertSame(1, $user->email_verified);
    }

    public function testVerifyEmailRejectEmptyToken():void {
        $result = VerifyEmailService::getInstance()->processVerification('');

        $this->assertFalse($result->success);
    }

    public function testVerifyEmailRejectInvalidToken():void {
        $token        = $this->newUserToken('invalidtoken');
        $invalidToken = $token . 'b';

        $result = VerifyEmailService::getInstance()->processVerification($invalidToken);

        $this->assertFalse($result->success);
    }

    public function testVerifyEmailRejectExpiredToken():void {
        $token = $this->newUserToken('expiredtoken');
        $user  = User::findByVerificationToken($token);
        $user->email_verification_token_expires_at = (new DateTime('-1 day'))->format('Y-m-d H:i:s');
        $user->save();

        $result = VerifyEmailService::getInstance()->processVerification($token);

        $this->assertFalse($result->success);
        $dedeletedUser = User::findByVerificationToken($token);
        $this->assertNull($dedeletedUser);
    }

    public function testVerifyEmailRejectAlreadyVerified():void {
        $token = $this->newUserToken('verified');
        $user  = User::findByVerificationToken($token);
        $user->email_verified = 1;
        $user->save();

        $result = VerifyEmailService::getInstance()->processVerification($token);

        $this->assertFalse($result->success);
    }

    public function testVerifyEmailChangeEmailSucceeds():void {
        $username = 'changeemail';
        $newemail = 'newemail@example.com';
        $token    = $this->newUserToken($username);
        $user     = User::findByVerificationToken($token);
        $user->email_verified = 1;
        $user->pending_email  = $newemail;
        $user->save();

        $result = VerifyEmailService::getInstance()->processVerification($token);

        $this->assertTrue($result->success);
        $updatedUser = User::findByUsername($username);
        $this->assertSame($newemail, $updatedUser->email);
        $this->assertNull($updatedUser->pending_email);
    }
}
