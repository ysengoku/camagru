import { api, endpoints } from '../api.js';
import { validator } from './helpers/validator.js';
import { initPasswordToggles } from './helpers/passwordVisibility.js';
import { showFieldError } from './helpers/formFeedback.js';
import { showToast, ToastMessage } from '../toast.js';

function validateSignupForm(username, email, password, confirmPassword) {
  let isValid = true;

  const isUsernameValid = validator.validateUsername(username);
  if (!isUsernameValid.valid) {
    showFieldError('username', isUsernameValid.message);
    isValid = false;
  }

  const isEmailValid = validator.validateEmail(email);
  if (!isEmailValid.valid) {
    showFieldError('email', isEmailValid.message);
    isValid = false;
  }

  const isPasswordValid = validator.validatePassword(
    username,
    password,
    confirmPassword
  );
  if (!isPasswordValid.valid) {
    showFieldError('password', isPasswordValid.message);
    showFieldError('confirm-password', isPasswordValid.message);
    isValid = false;
  }

  return isValid;
}

function showServerFeedback(errors) {
  if (errors.username) {
    showFieldError('username', errors.username);
  }
  if (errors.email) {
    showFieldError('email', errors.email);
  }
  if (errors.password) {
    showFieldError('password', errors.password);
  }
  if (errors.confirmPassword) {
    showFieldError('confirm-password', errors.confirmPassword);
  }
}

function clearFormErrors() {
  const errorFields = ['username', 'email', 'password', 'confirm-password'];
  errorFields.forEach((field) => {
    const errorElement = document.getElementById(`${field}-error`);
    if (errorElement) {
      errorElement.textContent = '';
    }
    const inputElement = document.getElementById(field);
    if (inputElement) {
      inputElement.classList.remove('input-error');
    }
  });
}

function init() {
  const signupForm = document.getElementById('signup-form');
  if (!signupForm) {
    return;
  }

  initPasswordToggles();

  const usernameInput = document.getElementById('username');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm-password');

  signupForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearFormErrors();

    const username = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (!validateSignupForm(username, email, password, confirmPassword)) {
      return;
    }

    try {
      const response = await api.post(endpoints.SIGNUP, {
        username,
        email,
        password,
      });
      if (response.ok) {
        window.location.href = `/login?toast=signup-success`;
      }
    } catch (error) {
      console.error('Signup error:', error.data);
      if (error.data && error.data.errors) {
        showServerFeedback(error.data.errors);
      } else {
        const formErrorElement = document.getElementById('form-error');
        if (formErrorElement) {
          formErrorElement.textContent =
            'An unexpected error occurred. Please try again later.';
        }
      }
    }
  });
}

init();
