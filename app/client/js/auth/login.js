import { showToast, ToastMessage } from '../toast.js';

const key = new URLSearchParams(window.location.search).get('toast');
if (key && ToastMessage[key]) {
  showToast(ToastMessage[key]);
  window.history.replaceState({}, '', '/login');
}
