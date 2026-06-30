export function showFieldError(field, message) {
  const errorElement = document.getElementById(`${field}-error`);
  if (errorElement) {
    errorElement.textContent = message;
  }
  const inputElement = document.getElementById(field);
  if (inputElement) {
    inputElement.classList.add('input-error');
  }
}

export function showServerFeedback(errors) {
  for (const [field, message] of Object.entries(errors)) {
    showFieldError(field, message);
  }
}
