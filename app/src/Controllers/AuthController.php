<?php

final class AuthController extends Controller {
    public function signup(): string {
        switch (Request::getMethod()) {
            case 'GET':
                if (SessionStore::activeSession()) {
                    Response::redirect('/');
                }

                return $this->render(['pageTitle' => 'Sign Up'], 'signup');
            case 'POST':
                if (SessionStore::activeSession()) {
                    return $this->json(['error' => 'Already logged in'], Response::BAD_REQUEST);
                }

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

        $res = VerifyEmailService::getInstance()->processVerification(Request::getQueryParam('token') ?? '');
        if (!$res->success) {
            return $this->json(['error' => $res->errors], $res->errors['status'] ?? Response::BAD_REQUEST);
        }
            
        return $this->json(['message' => $res->data['message'] ?? 'Email verified successfully'], Response::OK);
    }

    public function login(): string {
        switch (Request::getMethod()) {
            case 'GET':
                if (SessionStore::activeSession()) {
                    Response::redirect('/');
                }

                return $this->render(['pageTitle' => 'Login'], 'login');
            case 'POST':
                if (SessionStore::activeSession()) {
                    return $this->json(['error' => 'Already logged in'], Response::BAD_REQUEST);
                }

                $input = Request::getPostData();
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';

                $res = SessionService::getInstance()->processLogin($username, $password);
                if (!$res->success) {
                    return $this->json(['error' => $res->errors], Response::BAD_REQUEST);
                }

                return $this->json(['message' => 'Login successful'], Response::OK);
            default:
                return $this->methodNotAllowed();
        }
    }

    public function logout(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $result = SessionService::getInstance()->processLogout();
        if (!$result->success) {
            return $this->json(['error' => $result->errors], Response::BAD_REQUEST);
        }

        return $this->json(['message' => 'Logged out successfully'], Response::OK);
    }

    public function forgotPassword(): string {
        $method = Request::getMethod();
        switch ($method) {
            case 'GET':
                return $this->render([
                    'pageTitle' => 'Forgot Password',
                    'action' => 'reset-password'
                ], 'forgotPassword');
            case 'POST':
                $res = ForgotPasswordService::getInstance()->processForgotPassword(Request::getPostData()['email'] ?? '');
                if ($res->success) {
                    return $this->json(['message' => 'An email has been sent successfully.'], Response::OK);
                }

                return $this->json(['error' => $res->errors], $res->errors['status'] ?? Response::BAD_REQUEST);
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
                    'token'     => $token
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

        return $this->render(['pageTitle' => 'Email Sent', 'action' => $action], 'emailSent');
    }

    public function resendEmail(): string {
        if (Request::getMethod() !== 'POST') {
            return $this->methodNotAllowed();
        }

        $email = SessionStore::get(SessionKey::PendingEmail);
        $action = SessionStore::get(SessionKey::ResendEmailAction);
        if (empty($email) || empty($action)) {
            error_log('No pending email or action found in session for resending email');

            return $this->json(['message' => 'Email resent successfully'], Response::OK); // Return OK to avoid revealing information about the session state
        }

        $user = User::findByEmail($email);
        if ($user === null) {
            error_log("Resent email for non-existent email: $email");

            return $this->json(['message' => 'Email resent successfully'], Response::OK);  // Return OK to avoid revealing information about the session state
        }

        $secondsUntilEmailSendAllowed = SessionStore::secondsUntilEmailSendAllowed();
        if ($secondsUntilEmailSendAllowed > 0) {
            return $this->json([
                'error'          => 'Please wait before resending the email',
                'time_remaining' => $secondsUntilEmailSendAllowed
            ], Response::TOO_MANY_REQUESTS);
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
                    error_log("Attempted to resend password reset email for unverified email: $email");
                    
                    return $this->json(['message' => 'Email resent successfully'], Response::OK);  // Return OK to avoid revealing information about the session state
                }

                ForgotPasswordService::getInstance()->sendPasswordResetEmail($email, $user->password_reset_token);

                return $this->json(['message' => 'Reset password email resent successfully'], Response::OK);
            default:
                return $this->json(['error' => 'Invalid action for resending email'], Response::BAD_REQUEST);
        }        
    }
}
