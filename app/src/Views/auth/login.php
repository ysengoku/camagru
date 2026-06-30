<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/login" id="login-form" class="auth-form" novalidate>
        <h2>Login</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="username" class="block">Username</label>
            <input type="text" id="username" name="username" required class="form-input">
            <span id="username-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="password" class="block">Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" required class="form-input">
                <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                    <span class="icon-visible"><?= render_icon('visible') ?></span>
                    <span class="icon-invisible" hidden><?= render_icon('invisible') ?></span>
                </button>
            </div>
            <span id="password-error" class="error-feedback"></span>
        </div>

        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Login
            </button>
            <p class="mt-4 pt-4 text-center">
                Don't have an account? &nbsp;
                <a href="/signup" class="color-info-dark">
                    Sign up
                </a>
            </p>
            <p class="mt-4 text-center">
                <a href="/forgot-password" class="color-info-dark">
                Forgot your password?
                </a>
            </p>
        </div>
    </form>
</div>
