<?php
require_once __DIR__ . '/../components/avatar.php';
require_once __DIR__ . '/../components/emailField.php';
require_once __DIR__ . '/../components/icon.php';
require_once __DIR__ . '/../components/passwordField.php';
require_once __DIR__ . '/../components/usernameField.php';
require_once __DIR__ . '/avatarSelection.php';

/**
 * @var User|null $user
 */
if ($user === null) {
    throw new HTTPNotFoundException();
}

$notificationsEnabled = $user->email_notifications_enabled;
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

        <?= render_username_field($user) ?>
        <?= render_email_field($user) ?>

        <hr />
        
        <?= render_password_field('Change Password', 'password') ?>
        <?= render_password_field('Confirm New Password', 'confirm-password') ?>

        <hr />

        <div class="flex-col gap-1 mb-4">
            <label for="avatar" class="block color-gray-600">Profile Picture</label>
            <div id="avatar-preview">
                <?= render_avatar($user->username, 'large', $user->avatar !== null ? $user->avatar : null) ?>
            </div>
            <?= render_avatar_selection($user) ?>
        </div>

        <hr />

        <div class="flex-col gap-1 mb-4">
            <span class="color-gray-600 mb-2">Email Notifications</span>
            <label for="notifications" class="toggle-switch mb-4">
                <input type="checkbox" id="notifications" name="notifications" <?= $notificationsEnabled === 1 ? 'checked' : '' ?>>
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
