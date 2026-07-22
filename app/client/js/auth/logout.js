import { api, endpoints } from '../api.js';
import { showToast, ToastType, ToastMessage } from '../toast.js';

const logoutButton = document.getElementById('logout-button');
if (logoutButton) {
  logoutButton.addEventListener('click', async (event) => {
    event.preventDefault();
    try {
      await api.post(endpoints.LOGOUT);
      window.location.href = '/login';
    } catch (error) {
      showToast(ToastType.ERROR, ToastMessage['logout-failed']);
    }
  });
}
