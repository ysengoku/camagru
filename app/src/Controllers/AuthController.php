<?php

final class AuthController extends Controller {
    public function signup(): string {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'GET':
                // If authenticated, redirect to feed page
                if (isset($_SESSION['user_id'])) {
                    $response = new Response();
                    $response->redirect('/feed');
                }
                return $this->render(['pageTitle' => 'Sign Up', 'user' => null], 'signup');
            case 'POST':
                // Handle signup logic here
                break;
            default:
                $response = new Response();
                $response->sendApiResponse(
                    ['error' => 'Method Not Allowed'],
                    405,
                    'Method Not Allowed'
                );
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