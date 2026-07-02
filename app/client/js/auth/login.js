import { api, endpoints } from '../api.js';
import { initPasswordToggles } from './helpers/passwordVisibility.js';
import { showToast, ToastType, ToastMessage } from '../toast.js';

function showToastFromQueryParam() {
  const key = new URLSearchParams(window.location.search).get('toast');

  if (key && ToastMessage[key]) {
    showToast(
      ToastType.SUCCESS,
      ToastMessage[key] || 'Operation completed successfully.'
    );
    window.history.replaceState({}, '', '/login');
  }
}

function init() {
  const loginForm = document.getElementById('login-form');
  if (!loginForm) {
    return;
  }

  showToastFromQueryParam();
  initPasswordToggles();

  const usernameInputEl = document.getElementById('username');
  const passwordInputEl = document.getElementById('password');

  loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const username = usernameInputEl.value.trim();
    const password = passwordInputEl.value;

    if (!username || !password) {
      showToast(ToastType.ERROR, 'Please fill in both username and password.');
      return;
    }

    try {
      const response = await api.post(endpoints.LOGIN, { username, password });
      if (response.ok) {
        window.location.href = '/';
      }
    } catch (error) {
      const message =
        error.data?.error?.general || 'Failed to login. Please try again later.';
      showToast(ToastType.ERROR, message);
    }
  });
}

init();
