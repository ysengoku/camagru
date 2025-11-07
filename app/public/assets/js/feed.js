import { requestSignUp, requestLogin, requestPasswordReset, requestLogout } from './auth.js';

const signupButton = document.getElementById('signup-button');
const signupDialog = document.getElementById('signup-modal');
const signupForm = document.getElementById('signup-form');
const loginButton = document.getElementById('login-button');
const loginDialog = document.getElementById('login-modal');
const loginForm = document.getElementById('login-form');
const logoutButton = document.getElementById('logout-button');

/**
 * Set up Signup form
 */
if (signupButton) {
  signupButton.addEventListener('click', () => showModal(signupDialog, signupForm, [loginDialog]));

  const signupSubmitButton = document.getElementById('submit-signup');
  const signupCloseButton = document.getElementById('signup-close');
  signupSubmitButton?.addEventListener('click', requestSignUp);
  signupCloseButton?.addEventListener('click', () => closeModal(signupDialog, signupForm));
  setClearErrorMessageHandler(signupDialog, signupForm);
}

/**
 * Set up Login form
 */
if (loginButton) {
  loginButton.addEventListener('click', () => showModal(loginDialog, loginForm, [signupDialog]));

  const loginSubmitButton = document.getElementById('submit-login');
  const loginCloseButton = document.getElementById('login-close');
  loginSubmitButton?.addEventListener('click', requestLogin);
  loginCloseButton?.addEventListener('click', () => closeModal(loginDialog, loginForm));
  setClearErrorMessageHandler(loginDialog, loginForm);
}

/**
 * Set up Logout button
 */
logoutButton?.addEventListener('click', requestLogout);

/**
 * Shows a modal and closes other open modals.
 * @param {HTMLDialogElement} modal The modal to show
 * @param {HTMLFormElement} form The form inside the modal
 * @param {HTMLDialogElement[]} otherModals Array of other modals to close if open
 */
function showModal(modal, form, otherModals) {
  otherModals.forEach((modal) => {
    if (modal.open) {
      modal.close();
    }
  });
  form.reset();
  modal.showModal();
}

/**
 * Closes a modal and resets its form.
 * @param {HTMLDialogElement} modal The modal to close
 * @param {HTMLFormElement} form The form inside the modal
 */
function closeModal(modal, form) {
  form.reset();
  const errorMessage = modal.querySelector('.form-error');
  errorMessage?.classList.add('hidden');
  modal.close();
}

function setClearErrorMessageHandler(modal, form) {
  form.querySelectorAll('input').forEach((input) => {
    input.addEventListener('focus', () => {
      const errorMessage = modal.querySelector('.form-error');
      errorMessage?.classList.add('hidden');
    })
  })
}
