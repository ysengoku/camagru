<?php
require_once __DIR__ . '/../components/passwordField.php';
require_once __DIR__ . '/../components/usernameField.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/login" id="login-form" class="auth-form" novalidate>
        <h2>Welcome Back!</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>

        <?= render_username_field(null, true) ?>
        <?= render_password_field('Password', 'password', true, 'current-password') ?>

        <div class="my-4 pt-4 flex-col gap-2">
            <button type="submit" class="button-primary font-bold w-100">
                Login
            </button>
            <p class="mt-4 pt-4 text-center color-gray-600">
                Don't have account yet?&nbsp;
                <a href="/signup" class="font-bold text-decoration-none color-primary-600">
                    Sign up
                </a>
            </p>
            <p class="mt-4 text-center">
                <a href="/forgot-password" class="color-primary-600">
                Forgot your password?
                </a>
            </p>
        </div>
    </form>
</div>
