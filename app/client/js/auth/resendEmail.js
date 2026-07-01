import { api, endpoints } from "../api.js";
import { showToast, ToastType, ToastMessage } from "../toast.js";

function countDownTimer(button, timerEl, timerSecondsEl, cooldownTime) {
    let remainingTime = cooldownTime;

    const updateTimer = () => {
        if (remainingTime > 0) {
            timerEl.classList.remove('invisible');
            timerSecondsEl.textContent = remainingTime;
            button.disabled = true;
            --remainingTime;
        } else {
            timerEl.classList.add('invisible');
            button.disabled = false;
            clearInterval(timerInterval);
        }
    };

    updateTimer(); // Initial call to set the timer immediately
    const timerInterval = setInterval(updateTimer, 1000);
}

function init() {
    const resendEmailButton = document.getElementById('resend-email-button');
    if (!resendEmailButton) {
        return;
    }

    const cooldownTime = parseInt(resendEmailButton.dataset.cooldownRemaining, 10) || 0;
    const cooldownTimerEl = document.getElementById('cooldown-timer');
    const cooldownSecondsEl = document.getElementById('cooldown-seconds');
    
    if (cooldownTime > 0) {
        countDownTimer(resendEmailButton, cooldownTimerEl, cooldownSecondsEl, cooldownTime);
    }
    
    resendEmailButton.addEventListener('click', async () => {
        try {
            await api.post(endpoints.RESEND_EMAIL);
            countDownTimer(resendEmailButton, cooldownTimerEl, cooldownSecondsEl, 60);
            showToast(ToastType.SUCCESS, ToastMessage['email-resent']);
        } catch (error) {
            const message = error.data?.error || 'Failed to resend email. Please try again later.';
            const remainingTime = error.data?.remainingTime || 60; // Default to 60 seconds if not provided
            countDownTimer(resendEmailButton, cooldownTimerEl, cooldownSecondsEl, remainingTime);
            showToast(ToastType.ERROR, message);
        }
    });
}

init();
