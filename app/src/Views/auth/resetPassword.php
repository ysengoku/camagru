<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<div class="auth-form-container">
    <form method="POST" action="/api/reset-password" id="reset-password-form" class="auth-form" data-token="<?= htmlspecialchars($token) ?>" novalidate>
        <h2>Reset Password</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="password" class="block color-gray-600">New Password*</label>
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
            <label for="confirm-password" class="block color-gray-600">Confirm New Password*</label>
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
                Reset Password
            </button>
        </div>
    </form>
</div>