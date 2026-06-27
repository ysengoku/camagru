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
                return $this->json(['error' => 'Method Not Allowed'], Response::METHOD_NOT_ALLOWED);
        }
    }

    public function login(): void {
    }

    public function logout(): void {
    }

    public function forgotPassword(): string {
    }

    public function resetPassword(): string {
    }

    public function verifyEmail(): string {
    }
}