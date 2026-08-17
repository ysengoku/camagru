import { api, endpoints } from '../api.js';
import { initPasswordToggles } from './helpers/passwordVisibility.js';
import { showFieldError, clearFormErrors } from './helpers/formFeedback.js';
import { showToast } from '../toast.js';
import { Validator } from './helpers/validator.js';

function init() {
  const resetPasswordForm = document.getElementById('reset-password-form');
  if (!resetPasswordForm) {
    return;
  }

  initPasswordToggles();

  const newPasswordInputEl = document.getElementById('password');
  const confirmPasswordInputEl = document.getElementById('confirm-password');
  const token = resetPasswordForm.dataset.token;

  resetPasswordForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormErrors();

    const newPassword = newPasswordInputEl.value.trim();
    const confirmPassword = confirmPasswordInputEl.value.trim();
    const validator = new Validator();

    const isPasswordValid = validator.validatePassword(
      newPassword,
      confirmPassword
    );
    if (!isPasswordValid.valid) {
      showFieldError('password', isPasswordValid.message);
      showFieldError('confirm-password', isPasswordValid.message);
      return;
    }

    try {
      const response = await api.post(endpoints.RESET_PASSWORD, {
        token,
        new_password: newPassword,
      });
      if (response.ok) {
        window.location.href = '/login?toast=password-reset';
      }
    } catch (error) {
      const message =
        error.data?.error?.token ||
        error.data?.error?.password ||
        'Failed to reset password. Please try again later.';
      // Show toast
      showToast(message);
    }
  });
}

init();
