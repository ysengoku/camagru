<?php

final class AuthController extends Controller {
    public function signup(): string {
        switch (Request::getMethod()) {
            case 'GET':
                if (isset($_SESSION['user_id'])) {
                    (new Response())->redirect('/feed');
                }
                return $this->render(['pageTitle' => 'Sign Up', 'user' => null], 'signup');
            case 'POST':
                $input = Request::getPostData();
                $signupData = new SignupData($input['username'] ?? '', $input['email'] ?? '', $input['password'] ?? '');
                $result = SignupService::getInstance()->processSignup($signupData);
                if ($result['success']) {
                    return $this->json(
                        ['message' => 'User created successfully', 'username' => $result['user']->username, 'email' => $result['user']->email],
                        Response::CREATED
                    );
                }
                return $this->json(['errors' => $result['errors']], Response::BAD_REQUEST);
            default:
                return $this->methodNotAllowed();
        }
    }
    
    public function verifyEmail(): string {
        $method = Request::getMethod();
        if ($method !== 'GET') {
            return $this->methodNotAllowed();
        }

        $token = Request::getQueryParam('token');
        if (empty($token)) {
            return $this->json(['error' => 'Verification token is required'], Response::BAD_REQUEST);
        }

        $user = User::findByVerificationToken($token);
        if ($user === null) {
            return $this->json(['error' => 'Invalid verification token'], Response::NOT_FOUND);
        }

        if ($user->email_verified) {
            return $this->json(['message' => 'Email is already verified'], Response::OK);
        }

        $expiresAt = $user->verification_token_expires_at;
        if ($expiresAt === null || new DateTime($expiresAt) < new DateTime()) {
            $user->delete();
            return $this->json(['error' => 'Verification link has expired'], Response::BAD_REQUEST);
        }

        $user->email_verified = 1;
        if (!$user->save()) {
            return $this->json(['error' => 'Failed to verify email'], Response::INTERNAL_ERROR);
        }
            
        return $this->json(['message' => 'Email verified successfully'], Response::OK);
    }

    public function login(): string {
        switch (Request::getMethod()) {
            case 'GET':
                // TODO: Check session
                return $this->render(['pageTitle' => 'Login', 'user' => null], 'login');
            case 'POST':
                $input = Request::getPostData();
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';

                // TODO: Implement actual authentication logic
            default:
                return $this->methodNotAllowed();
        }
    }

    // public function logout(): void {
    // }

    public function forgotPassword(): string {
        $method = Request::getMethod();
        switch ($method) {
            case 'GET':
                // TODO: Check session
                return $this->render(['pageTitle' => 'Forgot Password', 'user' => null], 'forgotPassword');
            case 'POST':
                $result = ForgotPasswordService::getInstance()->processForgotPassword(Request::getPostData()['email'] ?? '');
                if ($result['success']) {
                    return $this->json(['message' => 'Password reset email sent'], Response::OK);
                }

                return $this->json(['errors' => $result['errors']], Response::BAD_REQUEST);
            default:
                return $this->methodNotAllowed();
        }
    }

    // public function resetPassword(): string {
    // }

    public function emailSent(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        return $this->render(['pageTitle' => 'Email Sent', 'user' => null], 'emailSent');
    }

    public function resendEmail(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $email = SessionStore::get(SessionKey::PendingEmail);
        $action = SessionStore::get(SessionKey::ResendEmailAction);
        if (empty($email) || empty($action)) {
            return $this->json(['error' => 'No pending email or action found'], Response::BAD_REQUEST);
        }

        $user = User::findByEmail($email);
        if ($user === null) {
            return $this->json(['error' => 'User not found'], Response::NOT_FOUND);
        }

        $lastEmailSentTime = SessionStore::get(SessionKey::LastEmailSentTime);
        if ($lastEmailSentTime !== null && (time() - $lastEmailSentTime) < 60) {
            return $this->json([
                'error' => 'Please wait before resending the email',
                'time_remaining' => 60 - (time() - $lastEmailSentTime)
                ],
                Response::TOO_MANY_REQUESTS
            );
        }

        switch ($action) {
            case 'verify':
                $token = $user->verification_token;
                $tokenExpiresAt = $user->verification_token_expires_at;
                if (empty($token) || empty($tokenExpiresAt) || new DateTime($tokenExpiresAt) < new DateTime()) {
                    return $this->json(['error' => 'Verification token has expired or is missing'], Response::BAD_REQUEST);
                }

                SignupService::getInstance()->sendVerificationEmail($email, $token);

                return $this->json(['message' => 'Verification email resent successfully'], Response::OK);
            case 'reset_password':
                $isVerified = $user->email_verified === 1;
                if (!$isVerified) {
                    return $this->json(['error' => 'Email is not verified. Cannot reset password.'], Response::BAD_REQUEST);
                }
                // TODO: Implement reset password email logic
                return $this->json(['message' => 'Reset password email resent successfully'], Response::OK);
            default:
                return $this->json(['error' => 'Invalid action for resending email'], Response::BAD_REQUEST);
        }        
    }
}
