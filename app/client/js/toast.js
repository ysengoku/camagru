export const ToastType = Object.freeze({
  SUCCESS: 'success',
  ERROR: 'danger',
  INFO: 'info',
});

export const ToastMessage = Object.freeze({
  'email-resent': 'Email resent. Please check your inbox.',
  'email-verified': 'Email verified successfully. Please login to continue.',
  'password-reset':
    'Password reset successful. Please log in with your new password.',
  'profile-update-success': 'Profile updated successfully.',
  'profile-update-error': 'Error updating profile. Please try again.',
  'webcam-access-error': 'Webcam is not accessible.',
});

export function showToast(type, message) {
  if (!message) {
    return;
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toast.popover = 'manual';
  document.body.appendChild(toast);

  toast.showPopover();
  requestAnimationFrame(() => toast.classList.add('toast-visible'));

  setTimeout(() => {
    toast.classList.remove('toast-visible');
    toast.addEventListener(
      'transitionend',
      () => {
        toast.hidePopover();
        toast.remove();
      },
      { once: true }
    );
  }, 4000);
}
