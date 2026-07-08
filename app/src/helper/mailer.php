<?php

require_once __DIR__ . '/../helper/renderer.php';

function sendVerificationLinkEmail(string $email, string $token, bool $isNewUser = true): void {
    $baseUrl = getenv('APP_BASE_URL');
    $baseUrl = $baseUrl !== false ? $baseUrl : '';
    $verificationLink = $baseUrl . "/verify-email?token={$token}";

    $assetsUrl = getenv('APP_ASSETS_URL');
    $logoUrl = $assetsUrl !== false ? $assetsUrl . 'img/logo.png' : '';

    $subject = 'Verify Your Email Address';
    $message = $isNewUser
        ? "Thank you for signing up! Click the button below to verify your email address and activate your account."
        : "You have requested to update your email address. Click the button below to complete the verification process.";
    $body = renderEmailTemplate('verification', ['logoUrl' => $logoUrl, 'verificationLink' => $verificationLink, 'message' => $message]);

    try {
        // EmailService::getInstance()->send($email, $subject, $body);
        SessionStore::set(SessionKey::LastEmailSentTime, time());
    } catch (Exception $e) {
        error_log("Failed to send verification email: " . $e->getMessage());
    }
}

function sendPasswordResetEmail(string $email, string $token): void {
    $baseUrl = getenv('APP_BASE_URL');
    $baseUrl = $baseUrl !== false ? $baseUrl : '';
    $resetLink = $baseUrl . "/reset-password?token=$token";

    $assetsUrl = getenv('APP_ASSETS_URL');
    $logoUrl = $assetsUrl !== false ? $assetsUrl . 'img/logo.png' : '';

    $subject = "Password Reset";
    $body = renderEmailTemplate('forgotPassword', ['logoUrl' => $logoUrl, 'resetLink' => $resetLink]);

    // EmailService::getInstance()->send($email, $subject, $body);
}
