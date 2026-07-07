<?php

function sendVerificationLinkEmail(string $email, string $token, bool $isNewUser = true): void {
    $verificationLink = getenv('APP_BASE_URL') . "/verify-email?token={$token}";
    $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
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
    $resetLink = getenv('APP_BASE_URL') . "/reset-password?token=$token";
    $subject = "Password Reset";
    $logoUrl = getenv('APP_ASSETS_URL') . 'img/logo.png';
    $body = renderEmailTemplate('forgotPassword', ['logoUrl' => $logoUrl, 'resetLink' => $resetLink]);

    // EmailService::getInstance()->send($email, $subject, $body);
}
