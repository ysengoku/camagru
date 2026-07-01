<?php
$email = SessionStore::get(SessionKey::PendingEmail);

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
        An email has been sent to 
        <span class="color-secondary-400">
            <?= htmlspecialchars($email) ?>
        </span>.
    </p>

    <div class="flex-col my-4 pt-4 gap-4">
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
        <a href="/login" class="button-primary font-bold text-center text-decoration-none">
            Go to Login
        </a>
    </div>
</div>
