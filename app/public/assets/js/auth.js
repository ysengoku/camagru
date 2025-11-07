import { ENDPOINTS } from './api.js';

const signupButton = document.getElementById('signup-button');
const signupDialog = document.getElementById('signup-modal');
const signupSubmitButton = document.getElementById('submit-signup');
const signupCloseButton = document.getElementById('signup-close');

signupButton?.addEventListener('click', showSignupForm);
signupSubmitButton?.addEventListener('click', requestSignUp);
signupCloseButton?.addEventListener('click', () => signupDialog?.close());

function showSignupForm() {
  signupDialog?.showModal();
};

async function requestSignUp(event) {
  event.preventDefault();
  const username = document.getElementById('signup-form-username')?.value;
  const email = document.getElementById('signup-form-email')?.value;
  const password = document.getElementById('signup-form-password')?.value;
  const passwordRepeat = document.getElementById('signup-form-password-repeat')?.value;
  console.log('Sign up requested', username, email, password, passwordRepeat);
  // TODO: Client side validation

  try {
    const response = await fetch('/api/signup', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, email, password, passwordRepeat }),
    });
    console.log('Response', response);
    const result = await response.json();
    if (result.success) {
      console.log('Sign up successfully', result);
      return;
    }
    console.error('Signup failed:', result.message);
  } catch (error) {
    console.error('Server error');
  }
}

const loginButton = document.getElementById('login-button');
const logoutButton = document.getElementById('logout-button');

loginButton?.addEventListener('click', showLoginForm);

function showLoginForm() {
  console.log('Login button pressed')
};

logoutButton?.addEventListener('click', requestLogout);
function requestLogout() {
  console.log('Logout requested');
  // Add handling
  logoutButton?.removeEventListener('click', requestLogout);
}