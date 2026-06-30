export function initPasswordToggles() {
  document.querySelectorAll('.password-toggle').forEach((button) => {
    const input = document.getElementById(button.dataset.target);
    const visibleIcon = button.querySelector('.icon-visible');
    const invisibleIcon = button.querySelector('.icon-invisible');
    if (!input || !visibleIcon || !invisibleIcon) {
      return;
    }

    button.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      visibleIcon.hidden = show;
      invisibleIcon.hidden = !show;
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
}
