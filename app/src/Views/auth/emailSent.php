<?php
$email = SessionStore::get(SessionKey::PendingEmail);
$action = $action ?? 'default';
$messageTemplates = [
    'default' => 'An email has been sent to %s.',
    'verify-email' => 'A verification email has been sent to %s.',
    'reset-password' => 'If an account exists, a password reset link has been sent to %s.',
];
$emailHtml = '<span class="color-primary-600">' . htmlspecialchars($email ?? '') . '</span>';
$message = sprintf($messageTemplates[$action] ?? $messageTemplates['default'], $emailHtml);

$cooldownTime = 60; // Cooldown time in seconds
$lastEmailSentTime = SessionStore::get(SessionKey::LastEmailSentTime);
$timeRemaining = 0;
if ($lastEmailSentTime !== null) {
    $timeElapsed = time() - $lastEmailSentTime;
    if ($timeElapsed < $cooldownTime) {
        $timeRemaining = $cooldownTime - $timeElapsed;
    }
}
?>

<div class="auth-form-container">
    <h2>Verify your Email</h2>
    <p class="color-gray-600">
        <?= $message ?>
    </p>

    <div class="flex-col my-4 pt-4 gap-!">
        <div class="flex align-center gap-1">
            <p class="color-gray-500">Didn't receive the email?</p>
            <button
                class="button-no-border font-bold"
                type="button" id="resend-email-button"
                data-cooldown-remaining="<?= $timeRemaining ?>"
                <?= $timeRemaining > 0 ? 'disabled' : '' ?>
            >
                Resend Email
            </button>
        </div>
        <span id="cooldown-timer" class="mb-4 color-danger font-size-3 <?= $timeRemaining > 0 ? '' : 'invisible' ?>">
            Please wait 
            <span id="cooldown-seconds"><?= $timeRemaining ?></span>
            second(s) before resending.
        </span>
        <a href="/login" class="button-primary font-bold text-center text-decoration-none mt-4">
            Go to Login
        </a>
    </div>
</div>
