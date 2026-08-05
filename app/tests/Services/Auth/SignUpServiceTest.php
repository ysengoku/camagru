<?php

final class SignupServiceTest extends DbTestCase {
    public function testProcessSignupCreateUser(): void {
        $testuser = [
            'username' => 'testuser',
            'email'    => 'testuser@example.com',
            'password' => 'Valid-Password123!'
        ];
        $data = new SignupData($testuser['username'], $testuser['email'], $testuser['password']);
        $result = SignupService::getInstance()->processSignup($data);

        $this->assertTrue($result->success);
        $this->assertSame($testuser['username'], $result->data['user']->username);
    }

    public function testProcessSignupRejectDuplicateUsername(): void {
        $username = 'duplicate';
        $password = 'Valid-Password123!';
    
        $userOne = new Signupdata($username, 'user_one@example.com', $password);
        SignupService::getInstance()->processSignup($userOne);

        $userTwo = new Signupdata($username, 'user_two@example.com', $password);
        $result = SignupService::getInstance()->processSignup($userTwo);
        
        $this->assertFalse($result->success);
        $this->assertArrayHasKey('username', $result->errors);
    }

    public function testProcessSignupRejectDuplicateEmail(): void {
        $email    = 'duplicate@example.com';
        $password = 'Valid-Password123!';
    
        $userOne = new Signupdata('userone', $email, $password);
        SignupService::getInstance()->processSignup($userOne);
        $verifiedUser = User::findByEmail($email);
        $verifiedUser->email_verified = 1;
        $verifiedUser->save();

        $userTwo = new Signupdata('usertwo', $email, $password);
        $result = SignupService::getInstance()->processSignup($userTwo);
        
        $this->assertFalse($result->success);
        $this->assertArrayHasKey('email', $result->errors);
    }

    public function testProcessSignupRejectInvalidPassword(): void {
        $testuser = [
            'username' => 'testuser',
            'email'    => 'testuser@example.com',
            'password' => 'InValidPassword123'
        ];
        $data = new SignupData($testuser['username'], $testuser['email'], $testuser['password']);
        $result = SignupService::getInstance()->processSignup($data);

        $this->assertFalse($result->success);
        $this->assertArrayHasKey('password', $result->errors);
    }
}
