import { api, endpoints } from '../api.js';
import { Validator } from './helpers/validator';
import {
  showFieldError,
  clearFormErrors,
  setButtonLoading,
  clearButtonLoading,
} from './helpers/formFeedback';

function init() {
  const forgotPasswordForm = document.getElementById('forgot-password-form');
  if (!forgotPasswordForm) {
    return;
  }

  const emailInputEl = document.getElementById('email');
  const submitButton = forgotPasswordForm.querySelector(
    'button[type="submit"]'
  );

  forgotPasswordForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormErrors();

    const email = emailInputEl.value.trim();
    const validator = new Validator();

    const isEmailValid = validator.validateEmail(email);
    if (!isEmailValid.valid) {
      showFieldError('email', isEmailValid.message);
      return;
    }

    const loadingText = 'Requesting password reset...';
    setButtonLoading(submitButton, loadingText);

    try {
      const response = await api.post(endpoints.PASSWORD_RESET_REQUEST, {
        email,
      });
      if (response.ok) {
        window.location.href = `/email-sent?action=reset-password`;
      }
      return;
    } catch (error) {
      const message =
        error.data?.error?.email ||
        'Failed to send password reset email. Please try again later.';
      showFieldError('email', message);
    }
    clearButtonLoading(submitButton);
  });
}

init();
