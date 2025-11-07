import { ENDPOINTS } from './api.js';

export async function requestSignUp(event) {
  event.preventDefault();
  const username = document.getElementById('signup-form-username')?.value;
  const email = document.getElementById('signup-form-email')?.value;
  const password = document.getElementById('signup-form-password')?.value;
  const passwordRepeat = document.getElementById('signup-form-password-repeat')?.value;

  const errorMessage = document.getElementById('signup-form-error');
  const errorMessageContent = document.getElementById('signup-form-error-message');

  // Client side validation
  const validators = [
    validateUsername(username),
    validateEmail(email),
    validatePassword(username, password, passwordRepeat),
  ];
  const failed = validators.find((v) => !v.valid);
  if (failed) {
    errorMessageContent.textContent = failed.message;
    errorMessage?.classList.remove('hidden');
  }

  try {
    const response = await fetch(ENDPOINTS.SIGNUP, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, email, password, passwordRepeat }),
    });
    const result = await response.json();
    if (result.success) {
      console.log('Sign up successfully.', result);
      // TODO: Show message: Check your email
      return;
    }
    errorMessageContent.textContent = result.message;
    errorMessage?.classList.remove('hidden');
  } catch (error) {
    console.error('Server error');
  }
}

export async function requestLogin(event) {
  event.preventDefault();
  const username = document.getElementById('login-form-username')?.value;
  const password = document.getElementById('login-form-password')?.value;

  try {
    const response = await fetch(ENDPOINTS.LOGIN, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password }),
    });
    const result = await response.json();
    if (result.success) {
      window.location.reload();
      return;
    }
    const errorMessage = document.getElementById('login-form-error');
    const messageContent = document.getElementById('login-form-error-message');
    messageContent.textContent = result.message;
    errorMessage?.classList.remove('hidden');
  } catch (error) {
    console.error('Server error');
  }
}

export function requestPasswordReset() {}

export function requestLogout() {
  console.log('Logout requested');
  // Add handling
}

function validateUsername(username) {
  const minLength = 3;
  const maxLength = 20;
  const regex = /^[a-zA-Z0-9_-]+$/;

  if (username.length < minLength) {
    return {
      valid: false,
      message: `Username must be at least ${minLength} characters long`,
    };
  }
  if (username.length > maxLength) {
    return {
      valid: false,
      message: `Username must not exceed ${maxLength} characters`,
    };
  }
  if (!regex.test(username)) {
    return {
      valid: false,
      message: 'Username can only contain letters, numbers, underscore, and hyphen',
    };
  }
  return { valid: true };
}

function validateEmail(email) {
  const maxLength = 254;
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (email.length > maxLength) {
    return {
      valid: false,
      message: `Email must not exceed ${maxLength} characters`,
    };
  }
  if (!regex.test(email)) {
    return { valid: false, message: 'Email format is invalid' };
  }
  return { valid: true };
}

function validatePassword(username, password, passwordRepeat) {
  const minLength = 12;
  const maxLength = 72;

  if (password !== passwordRepeat) {
    return {
      valid: false,
      message: 'The password and password confirmation do not match',
    };
  }
  if (password.length < minLength) {
    return {
      valid: false,
      message: `Password must be at least ${minLength} characters long`,
    };
  }
  if (password.length > maxLength) {
    return {
      valid: false,
      message: `Password must not exceed ${maxLength} characters`,
    };
  }
  if (password.toLowerCase().includes(username.toLowerCase())) {
    return { valid: false, message: 'Password must not contain the username' };
  }
  if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
    return {
      valid: false,
      message:
        'Password must contain at least one lowercase, \
        one uppercase, and one digit',
    };
  }
  if (/[^a-zA-Z0-9]/.test(password)) {
    return {
      valid: false,
      message: 'Password may contain only alphanumeric characters',
    };
  }
  return { valid: true };
}
