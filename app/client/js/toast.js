const ToastType = Object.freeze({
  SUCCESS: 'success',
  DANGER: 'danger',
  INFO: 'info',
});

export const ToastMessage = Object.freeze({
  'signup-success': {
    message: 'Signup successful! Please check your email to verify your account.',
    type: ToastType.SUCCESS,
  }
});

export function showToast(message) {
  if (typeof message !== 'object' || !message.message || !message.type) {
    return;
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${message.type}`;
  toast.textContent = message.message;
  toast.popover = 'manual';
  document.body.appendChild(toast);

  toast.showPopover();
  requestAnimationFrame(() => toast.classList.add('toast-visible'));

  setTimeout(() => {
    toast.classList.remove('toast-visible');
    toast.addEventListener('transitionend', () => {
      toast.hidePopover();
      toast.remove();
    }, { once: true });
  }, 4000);
}
