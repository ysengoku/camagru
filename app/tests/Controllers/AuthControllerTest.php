<?php

final class AuthControllerTest extends DbTestCase {
    private function createVerifiedUser(string $username, string $password): User {
        $data = new SignupData($username, "{$username}@example.com", $password);
        SignupService::getInstance()->processSignup($data);
        $user = User::findByUsername($username);
        $user->email_verified = 1;
        $user->save();

        return $user;
    }

    // ===== POST /api/signup =================================================

    public function testSignupSucceeds(): void {
        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['username' => 'newsignupuser', 'email' => 'newsignupuser@example.com', 'password' => 'Valid-Password123!'];

        $result = $controller->signup();
        $data = json_decode($result, true);

        $this->assertSame(Response::CREATED, $controller->getStatus()['code']);
        $this->assertSame('User created successfully', $data['message']);
    }

    public function testSignupRejectsInvalidInput(): void {
        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['username' => 'newsignupuser2', 'email' => 'newsignupuser2@example.com', 'password' => 'invalid'];

        $result = $controller->signup();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== POST /api/login ==================================================

    public function testLoginSucceeds(): void {
        $password = 'Valid-Password123!';
        $this->createVerifiedUser('loginroutesuccess', $password);

        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['username' => 'loginroutesuccess', 'password' => $password];

        $result = $controller->login();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame('Login successful', $data['message']);
    }

    public function testLoginRejectsWrongPassword(): void {
        $this->createVerifiedUser('loginroutewrongpw', 'Valid-Password123!');

        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['username' => 'loginroutewrongpw', 'password' => 'Wrong-Password123!'];

        $result = $controller->login();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== POST /api/logout =================================================

    public function testLogoutSucceeds(): void {
        SessionStore::set(SessionKey::UserId, 1);

        $controller = new AuthController();
        $result = $controller->logout();
        $data = json_decode($result, true);

        // logout() calls session_destroy(); PHPUnit runs the whole suite in one
        // process, so leaving the session inactive here breaks session_regenerate_id()
        // in later, unrelated tests. Restart it so the rest of the suite is unaffected.
        session_start();

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame('Logged out successfully', $data['message']);
    }

    public function testLogoutRejectsNoActiveSession(): void {
        $controller = new AuthController();
        $result = $controller->logout();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== POST /api/forgot-password ========================================

    public function testForgotPasswordSucceeds(): void {
        $this->createVerifiedUser('forgotpwroute', 'Valid-Password123!');
        SessionStore::delete(SessionKey::LastEmailSentTime);

        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['email' => 'forgotpwroute@example.com'];

        $result = $controller->forgotPassword();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertArrayHasKey('message', $data);
    }

    public function testForgotPasswordRejectsInvalidEmail(): void {
        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['email' => 'not-an-email'];

        $result = $controller->forgotPassword();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== POST /api/reset-password =========================================

    public function testResetPasswordSucceeds(): void {
        $email = 'resetpwroute@example.com';
        $this->createVerifiedUser('resetpwroute', 'Valid-Password123!');
        SessionStore::delete(SessionKey::LastEmailSentTime);
        ForgotPasswordService::getInstance()->processForgotPassword($email);
        $token = User::findByEmail($email)->password_reset_token;

        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['token' => $token, 'new_password' => 'New-Valid-Password123!'];

        $result = $controller->resetPassword();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertSame('Password reset successfully', $data['message']);
    }

    public function testResetPasswordRejectsInvalidToken(): void {
        $controller = new AuthController();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['token' => 'not-a-real-token', 'new_password' => 'New-Valid-Password123!'];

        $result = $controller->resetPassword();
        $data = json_decode($result, true);

        $this->assertSame(Response::BAD_REQUEST, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
    }

    // ===== POST /api/resend-email =============================================

    public function testResendEmailSucceeds(): void {
        $user = $this->createVerifiedUser('resendemailroute', 'Valid-Password123!');
        $user->email_verified = 0;
        $user->email_verification_token = 'a-token';
        $user->email_verification_token_expires_at = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $user->save();

        SessionStore::set(SessionKey::PendingEmail, $user->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::Signup->value);
        SessionStore::delete(SessionKey::LastEmailSentTime);

        $controller = new AuthController();
        $result = $controller->resendEmail();
        $data = json_decode($result, true);

        $this->assertSame(Response::OK, $controller->getStatus()['code']);
        $this->assertArrayHasKey('message', $data);
    }

    public function testResendEmailRejectsRateLimit(): void {
        $user = $this->createVerifiedUser('resendratelimit', 'Valid-Password123!');
        $user->email_verified = 0;
        $user->email_verification_token = 'a-token';
        $user->email_verification_token_expires_at = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $user->save();

        SessionStore::set(SessionKey::PendingEmail, $user->email);
        SessionStore::set(SessionKey::ResendEmailAction, EmailAction::Signup->value);
        SessionStore::set(SessionKey::LastEmailSentTime, time());

        $controller = new AuthController();
        $result = $controller->resendEmail();
        $data = json_decode($result, true);

        $this->assertSame(Response::TOO_MANY_REQUESTS, $controller->getStatus()['code']);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('time_remaining', $data);
    }
}
