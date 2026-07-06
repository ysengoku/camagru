<?php
    require_once __DIR__ . '/../components/icon.php';
    require_once __DIR__ . '/../components/avatar.php';
    require_once __DIR__ . '/avatarSelection.php';

    $notificationsEnabled = $user->email_notifications_enabled ?? false;
?>

<dialog id="password-confirmation-dialog" class="dialog">
    <div class="flex-col gap-1 my-4">
        <label for="current-password" class="block color-gray-600 mb-4">Please Enter Your Current Password</label>
        <input type="password" id="current-password" name="current_password" data-sensitive="true" class="form-input">
    </div>
    <div class="flex mt-4 mb-3 pt-4 gap-2">
        <button type="button" class="button-no-border font-bold w-100" id="cancel-current-password-button">
            Cancel
        </button>
        <button type="submit" class="button-primary font-bold w-100" id="confirm-current-password-button">
            Confirm
        </button>
    </div>
</dialog>

<div class="auth-form-container">
    <form method="POST" action="/api/profile" id="profile-form" class="auth-form" novalidate>
        <h2>Manage Your Account</h2>
        <div class="flex-col gap-1 mb-4">
            <span id="form-error" class="error-feedback"></span>
        </div>

        <div class="flex-col gap-1 mb-4">
            <label for="username" class="block color-gray-600">Username</label>
            <input type="text" id="username" name="username" class="form-input" value="<?= htmlspecialchars($user->username ?? '') ?>">
            <span id="username-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="email" class="block color-gray-600">Email</label>
            <input type="email" id="email" name="email" data-sensitive="true" class="form-input" value="<?= htmlspecialchars($user->email ?? '') ?>">
            <span id="email-error" class="error-feedback"></span>
        </div>

        <hr />

        <div class="flex-col gap-1 mb-4">
            <label for="password" class="block color-gray-600">Change Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" data-sensitive="true" class="form-input">
                <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                    <span class="icon-visible"><?= render_icon('visible') ?></span>
                    <span class="icon-invisible" hidden><?= render_icon('invisible') ?></span>
                </button>
            </div>
            <span id="password-error" class="error-feedback"></span>
        </div>
        <div class="flex-col gap-1 mb-4">
            <label for="confirm-password" class="block color-gray-600">Confirm New Password</label>
            <div class="password-field">
                <input type="password" id="confirm-password" name="confirm-password" class="form-input">
                <button type="button" class="password-toggle" data-target="confirm-password" aria-label="Show password">
                    <span class="icon-visible"><?= render_icon('visible') ?></span>
                    <span class="icon-invisible" hidden><?= render_icon('invisible') ?></span>
                </button>
            </div>
            <span id="confirm-password-error" class="error-feedback"></span>
        </div>

        <hr />


        <div class="flex-col gap-1 mb-4">
            <label for="avatar" class="block color-gray-600">Profile Picture</label>
            <div id="avatar-preview">
                <?= render_avatar($user->username, 'large', $user->avatar ?? null) ?>
            </div>
            <?= render_avatar_selection($user) ?>
        </div>

        <hr />

        <div class="flex-col gap-1 mb-4">
            <span class="color-gray-600 mb-2">Email Notifications</span>
            <label for="notifications" class="toggle-switch mb-4">
                <input type="checkbox" id="notifications" name="notifications" <?= $notificationsEnabled ? 'checked' : '' ?>>
                <span class="toggle-slider toggle-slider-round"></span>
            </label>
        </div>

        <div class="my-4 pt-4">
            <button type="submit" class="button-primary font-bold w-100">
                Update Profile
            </button>
        </div>
    </form>
</div>
