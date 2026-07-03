<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/signup" id="signup-form" class="auth-form" novalidate>
        <h2>Create Account</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="username" class="block color-gray-600">Username*</label>
            <input type="text" id="username" name="username" required class="form-input">
            <span id="username-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="email" class="block color-gray-600">Email*</label>
            <input type="email" id="email" name="email" required class="form-input">
            <span id="email-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="password" class="block color-gray-600">Password*</label>
            <div class="password-field">
                <input type="password" id="password" name="password" required class="form-input">
                <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                    <span class="icon-visible"><?= render_icon('visible') ?></span>
                    <span class="icon-invisible" hidden><?= render_icon('invisible') ?></span>
                </button>
            </div>
            <span id="password-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="confirm-password" class="block color-gray-600">Confirm Password*</label>
            <div class="password-field">
                <input type="password" id="confirm-password" name="confirm-password" required class="form-input">
                <button type="button" class="password-toggle" data-target="confirm-password" aria-label="Show password">
                    <span class="icon-visible"><?= render_icon('visible') ?></span>
                    <span class="icon-invisible" hidden><?= render_icon('invisible') ?></span>
                </button>
            </div>
            <span id="confirm-password-error" class="error-feedback"></span>
        </div>

        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Sign Up
            </button>
            <p class="mt-4 pt-4 text-center">
                Already have an account? &nbsp;
                <a href="/login" class="font-bold text-decoration-none color-primary-600">
                    Login
                </a>
          </p>
        </div>
    </form>
</div>
