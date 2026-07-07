<?php
    require_once __DIR__ . '/../components/emailField.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/forgot-password" id="forgot-password-form" class="auth-form" novalidate>
        <h2>Forgot Password</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4 pb-4">
            <p class="color-primary-600">
                Enter your email address to receive a link to reset your password.
            </p>
        </div>
        <?= render_email_field(null, true) ?>
        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Reset Password
            </button>
        </div>
    </form>
</div>
