<?php
    require_once __DIR__ . '/../components/icon.php';
    require_once __DIR__ . '/../components/passwordField.php';

    $token = $token ?? '';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/reset-password" id="reset-password-form" class="auth-form" data-token="<?= htmlspecialchars($token) ?>" novalidate>
        <h2>Reset Password</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>

        <?= render_password_field('New Password', 'password', true) ?>
        <?= render_password_field('Confirm New Password', 'confirm-password', true) ?>

        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Reset Password
            </button>
        </div>
    </form>
</div>