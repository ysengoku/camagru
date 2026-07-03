import { api, endpoints } from '../api.js';

const logoutButton = document.getElementById('logout-button');
if (logoutButton) {
  logoutButton.addEventListener('click', async (event) => {
    event.preventDefault();
    try {
      await api.post(endpoints.LOGOUT);
      window.location.href = '/login';
    } catch (error) {
      console.error('Logout failed:', error);
    }
  });
}
