<?php

final class AuthController extends Controller {
    public function signup(): string {
        $currentUserId = SessionStore::getCurrentUserId();

        switch (Request::getMethod()) {
            case 'GET':
                if ($currentUserId) {
                    Response::redirect('/feed');
                }

                return $this->render(['pageTitle' => 'Sign Up', 'user' => null], 'signup');
            case 'POST':
                $input = Request::getPostData();
                $signupData = new SignupData($input['username'] ?? '', $input['email'] ?? '', $input['password'] ?? '');
                $result = SignupService::getInstance()->processSignup($signupData);
                if ($result->success) {
                    return $this->json(
                        ['message' => 'User created successfully'],
                        Response::CREATED
                    );
                }
                return $this->json(['error' => $result->errors], Response::BAD_REQUEST);
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
            return $this->json(['error' => 'Email is already verified'], Response::OK);
        }

        $expiresAt = $user->verification_token_expires_at;
        if ($expiresAt === null || new DateTime($expiresAt) < new DateTime()) {
            $user->delete();
            return $this->json(['error' => 'Verification link has expired'], Response::BAD_REQUEST);
        }

        $user->email_verified = 1;
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
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

                $res = LoginService::getInstance()->processLogin($username, $password);
                if (!$res->success) {
                    return $this->json(['error' => $res->errors], Response::BAD_REQUEST);
                }

                return $this->json(['message' => 'Login successful'], Response::OK);
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
                return $this->render([
                    'pageTitle' => 'Forgot Password',
                    'user' => null,
                    'action' => 'reset-password'
                ], 'forgotPassword');
            case 'POST':
                $result = ForgotPasswordService::getInstance()->processForgotPassword(Request::getPostData()['email'] ?? '');
                if ($result->success) {
                    return $this->json(['message' => 'An email has been sent successfully.'], Response::OK);
                }

                return $this->json(['error' => $result->errors], Response::BAD_REQUEST);
            default:
                return $this->methodNotAllowed();
        }
    }

    public function resetPassword(): string {
        $method = Request::getMethod();
        switch ($method) {
            case 'GET':
                $token = Request::getQueryParam('token') ?? '';
                $validationResult = ResetPasswordService::getInstance()->validateToken($token);
                if (!$validationResult->success) {
                    throw new HTTPNotFoundException();
                }

                return $this->render([
                    'pageTitle' => 'Reset Password',
                    'user' => null,
                    'token' => $token
                ], 'resetPassword');
            case 'POST':
                $input = Request::getPostData();
                $result = ResetPasswordService::getInstance()->processResetPassword($input['token'] ?? '', $input['new_password'] ?? '');
                if ($result->success) {
                    return $this->json(['message' => 'Password reset successfully'], Response::OK);
                }

                return $this->json(['error' => $result->errors], Response::BAD_REQUEST);
            default:
                return $this->methodNotAllowed();
        }
    }

    public function emailSent(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }
        
        $action = Request::getQueryParam('action') ?? '';
        if (!in_array($action, ['verify-email', 'reset-password'])) {
            throw new HTTPNotFoundException();
        }

        return $this->render(['pageTitle' => 'Email Sent', 'user' => null, 'action' => $action], 'emailSent');
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

                ForgotPasswordService::getInstance()->sendPasswordResetEmail($email, $user->password_reset_token);

                return $this->json(['message' => 'Reset password email resent successfully'], Response::OK);
            default:
                return $this->json(['error' => 'Invalid action for resending email'], Response::BAD_REQUEST);
        }        
    }
}
