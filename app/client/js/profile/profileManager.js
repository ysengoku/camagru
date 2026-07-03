import { api, endpoints } from '../api.js';
import { showFieldError } from '../auth/helpers/formFeedback.js';
import { showToast, ToastType, ToastMessage } from '../toast.js';
import { validator } from '../auth/helpers/validator.js';

function init() {
  const form = document.getElementById('profile-form');
  if (!form) {
    return;
  }

  const usernameInput = document.getElementById('username');
  const emailInput = document.getElementById('email');
  const currentPasswordInput = document.getElementById('current-password');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm-password');
  
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    const inputData = {
      username: usernameInput.value.trim(),
      email: emailInput.value.trim(),
      currentPassword: currentPasswordInput.value,
      password: passwordInput.value,
      confirmPassword: confirmPasswordInput.value,
    };

    const isValid = validateInput(inputData);
    if (!isValid) {
      return;
    }

    try {
      await api.post(endpoints.PROFILE, inputData);
      showToast(ToastType.SUCCESS, ToastMessage.PROFILE_UPDATE_SUCCESS);
    } catch (error) {
      console.error('Error submitting profile data:', error);
      showToast(ToastType.ERROR, ToastMessage.PROFILE_UPDATE_ERROR);
    }
  });

  const container = document.querySelector('.avatar-selection');
  if (container) {
    container.addEventListener('change', (event) => {
      updateAvatarPreview(event.target);
    });
  }
}

function validateInput(inputData) {
  let isValid = true;
  
  const isUsernameValid = validator.validateUsername(inputData.username);
  if (!isUsernameValid.valid) {
    showFieldError('username', isUsernameValid.message);
    isValid = false;
  }
  
  const isEmailValid = validator.validateEmail(inputData.email);
  if (!isEmailValid.valid) {
    showFieldError('email', isEmailValid.message);
    isValid = false;
  }

  if ((inputData.password || inputData.confirmPassword) && !inputData.currentPassword) {
    showFieldError('current-password', 'Current password is required to update password.');
    isValid = false;
  }
  
  const isPasswordValid = validator.validatePassword(inputData.password, inputData.confirmPassword);
  if (!isPasswordValid.valid) {
    showFieldError('password', isPasswordValid.message);
    showFieldError('confirm-password', isPasswordValid.message);
    isValid = false;
  }
  
  return isValid;
}

function updateAvatarPreview(target) {
  if (!target.matches('.avatar-selection-radio')) {
    return;
  }

  const preview = document.getElementById('avatar-preview');
  let img = preview.querySelector('img');
  if (!img) {
    preview.innerHTML = '';
    img = document.createElement('img');
    img.className = 'avatar avatar-large';
    preview.appendChild(img);
  }
  img.src = target.value;
} 

init();
