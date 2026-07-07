<?php
require_once __DIR__ . '/../components/emailField.php';
require_once __DIR__ . '/../components/passwordField.php';
require_once __DIR__ . '/../components/usernameField.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/signup" id="signup-form" class="auth-form" novalidate>
        <h2>Create Account</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <?= render_username_field(null, true) ?>
        <?= render_email_field(null, true) ?>
        <?= render_password_field('Password', 'password', true) ?>
        <?= render_password_field('Confirm Password', 'confirm-password', true) ?>

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
