<?php

final class SessionServiceTest extends DbTestCase {
    private function createVerifiedUser(string $username, string $password): void {
        $data = new SignupData($username, "{$username}@example.com", $password);
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();
    }

    public function testProcessLoginSuccessfully(): void {
        $username = 'loginsuccess';
        $password = 'Valid-Password123!';
        $this->createVerifiedUser($username, $password);
        $result = SessionService::getInstance()->processLogin($username, $password);

        $this->assertTrue($result->success);
    }

    public function testProcessLoginRejectWrongPassword(): void {
        $username = 'loginwrongpw';
        $password = 'Valid-Password123!';
        $wrongPassword = 'Wrong-Password123!';
        $this->createVerifiedUser($username, $password);
        $result = SessionService::getInstance()->processLogin($username, $wrongPassword);

        $this->assertFalse($result->success);
    }

    public function testProcessLoginRejectUnknownUsername(): void {
        $password = 'Valid-Password123!';
        $result = SessionService::getInstance()->processLogin('unknownusername', $password);

        $this->assertFalse($result->success);
    }

    public function testProcessLoginRejectUnverifiedEmail(): void {
        $username = 'unverified';
        $password = 'Valid-Password123!';
        $data = new SignupData($username, "{$username}@example.com", $password);
        SignupService::getInstance()->processSignup($data);

        $result = SessionService::getInstance()->processLogin($username, $password);

        $this->assertFalse($result->success);
    }
}
