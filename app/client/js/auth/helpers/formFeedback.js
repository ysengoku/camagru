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

export function clearFormErrors() {
  const errorElements = document.querySelectorAll('.error-feedback');
  errorElements.forEach((el) => {
    el.textContent = '';
  });

  const inputElements = document.querySelectorAll('.input-error');
  inputElements.forEach((el) => {
    el.classList.remove('input-error');
  });
}
