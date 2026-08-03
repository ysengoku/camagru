<?php

require_once __DIR__ . '/../helper/mailer.php';

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class AuthController extends Controller {
    /**
     * Create a new user account.
     * 
     * @route POST /api/signup
     * @bodyParam string $username
     * @bodyParam string $email
     * @bodyParam string $password
     * @response 201 {message} User created successfully
     * @response 400 {error} Validation failed
     */
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

    public function verifyEmail(): void {
        $res = VerifyEmailService::getInstance()->processVerification(Request::getQueryParam('token') ?? '');
        if (!$res->success) {
            throw new HTTPNotFoundException();
        }

        Response::redirect('/login?toast=email-verified');
    }

    /**
     * Authenticate a user by username and password, starting a new session.
     * 
     * @route POST /api/login
     * @bodyParam string $username
     * @bodyParam string $password
     * @response 200 {message} Login successful
     * @response 400 {error} Authentication failed
     */
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

    /**
     * Clear and destroy the current user session
     * 
     * @route POST /api/logout
     * @response 200 {message} Logged out successfully
     * @response 400 {error} Logout failed
     */
    public function logout(): string {
        $result = SessionService::getInstance()->processLogout();
        if (!$result->success) {
            return $this->json(['error' => $result->errors], Response::BAD_REQUEST);
        }

        return $this->json(['message' => 'Logged out successfully'], Response::OK);
    }

    /**
     * Sends a password-reset email to the given address, if it belongs to a registered account.
     *
     * @route POST /api/forgot-password
     * @bodyParam string $email
     * @response 200 {message} An email has been sent successfully
     * @response 400 {error} Failed to send an email
     */
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

    /**
     * Resets a user's password using a valid password-reset token.
     *
     * @route POST /api/reset-password
     * @bodyParam string $token
     * @bodyParam string $new_password
     * @response 200 {message} Password reset successfully
     * @response 400 {error} Validation failed
     */
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
        $action = Request::getQueryParam('action') ?? '';
        if (!in_array($action, ['verify-email', 'reset-password'])) {
            throw new HTTPNotFoundException();
        }

        return $this->render(['pageTitle' => 'Email Sent', 'action' => $action], 'emailSent');
    }

    /**
     * Resends the pending verification or password-reset email tied to the current session.
     *
     * @route POST /api/resend-email
     * @response 200 {message} Email resent successfully
     * @response 400 {error} Verification token has expired or is missing, or invalid action
     * @response 429 {error, time_remaining} Too many requests, wait before resending
     */
    public function resendEmail(): string {
        $email = SessionStore::get(SessionKey::PendingEmail);
        $action = SessionStore::get(SessionKey::ResendEmailAction);
        if (!is_string($email) || $email === '' || !is_string($action) || $action === '') {
            error_log('No pending email or action found in session for resending email');

            return $this->json(['message' => 'Email resent successfully'], Response::OK); // Return OK to avoid revealing information about the session state
        }

        $user = User::findByEmail($email);
        if ($user === null) {
            error_log("Resent email for non-existent email: $email");

            return $this->json(['message' => 'Email resent successfully'], Response::OK); // Return OK to avoid revealing information about the session state
        }

        $secondsUntilEmailSendAllowed = SessionStore::secondsUntilEmailSendAllowed();
        if ($secondsUntilEmailSendAllowed > 0) {
            return $this->json([
                'error'          => 'Please wait before resending the email',
                'time_remaining' => $secondsUntilEmailSendAllowed
            ], Response::TOO_MANY_REQUESTS);
        }

        switch ($action) {
            case 'signup':
                $token = $user->email_verification_token;
                $tokenExpiresAt = $user->email_verification_token_expires_at;
                if ($token === null || $token === '' || $tokenExpiresAt === null || $tokenExpiresAt === '' || new DateTime($tokenExpiresAt) < new DateTime()) {
                    return $this->json(['error' => 'Verification token has expired or is missing'], Response::BAD_REQUEST);
                }

                sendVerificationLinkEmail($email, $token);

                return $this->json(['message' => 'Verification email resent successfully'], Response::OK);
            case 'email_change':
                $token = $user->email_verification_token;
                $tokenExpiresAt = $user->email_verification_token_expires_at;
                if ($token === null || $token === '' || $tokenExpiresAt === null || $tokenExpiresAt === '' || new DateTime($tokenExpiresAt) < new DateTime()) {
                    return $this->json(['error' => 'Verification token has expired or is missing'], Response::BAD_REQUEST);
                }

                sendVerificationLinkEmail($email, $token, false);

                return $this->json(['message' => 'Email change verification email resent successfully'], Response::OK);
            case 'reset_password':
                $isVerified = $user->email_verified === 1;
                if (!$isVerified) {
                    error_log("Attempted to resend password reset email for unverified email: $email");
                    
                    return $this->json(['message' => 'Email resent successfully'], Response::OK);  // Return OK to avoid revealing information about the session state
                }

                $resetToken = $user->password_reset_token;
                if ($resetToken === null || $resetToken === '') {
                    return $this->json(['error' => 'Password reset token has expired or is missing'], Response::BAD_REQUEST);
                }

                sendPasswordResetEmail($email, $resetToken);

                return $this->json(['message' => 'Reset password email resent successfully'], Response::OK);
            default:
                return $this->json(['error' => 'Invalid action for resending email'], Response::BAD_REQUEST);
        }        
    }
}
