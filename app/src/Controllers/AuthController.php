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

    // public function forgotPassword(): string {
    // }

    // public function resetPassword(): string {
    // }

}