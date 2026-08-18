import { api, endpoints } from '../api.js';
import { initPasswordToggles } from '../auth/helpers/passwordVisibility.js';
import {
  showFieldError,
  clearFormErrors,
  showServerFeedback,
  setButtonLoading,
  clearButtonLoading,
} from '../auth/helpers/formFeedback.js';
import { showToast, ToastType, ToastMessage } from '../toast.js';
import { validator } from '../auth/helpers/validator.js';

function init() {
  const form = document.getElementById('profile-form');
  if (!form) {
    return;
  }
  const submitButton = form.querySelector('button[type="submit"]');

  const initialValues = new Map(
    Array.from(form.elements)
      .filter((el) => el.name && !(el.type === 'radio' && !el.checked))
      .map((el) => [el.name, currentValue(el)])
  );

  function currentValue(el) {
    if (el.type === 'checkbox') {
      return el.checked;
    }
    if (el.type === 'radio') {
      return el.checked ? el.value : null;
    }
    return el.value;
  }

  function hasChanges() {
    function isFirstRadioGroupOccurrence(el) {
      const seenRadioGroups = new Set();
      if (el.type !== 'radio') {
        return true;
      }
      if (seenRadioGroups.has(el.name)) {
        return false;
      }
      seenRadioGroups.add(el.name);
      return true;
    }

    return Array.from(form.elements)
      .filter((el) => el.name)
      .filter(isFirstRadioGroupOccurrence)
      .some((el) => {
        if (el.type === 'radio') {
          const checked = form.querySelector(
            `input[name="${el.name}"]:checked`
          );
          return (checked?.value ?? null) !== initialValues.get(el.name);
        }
        return currentValue(el) !== initialValues.get(el.name);
      });
  }

  function hasSensitiveChanges() {
    return Array.from(form.elements)
      .filter((el) => el.dataset.sensitive === 'true')
      .some((el) => el.value !== initialValues.get(el.name));
  }

  function buildInputData() {
    return Object.fromEntries(
      Array.from(form.elements)
        .filter((el) => el.name && !(el.type === 'radio' && !el.checked))
        .map((el) => [el.name, currentValue(el)])
    );
  }

  function validateInput() {
    let isValid = true;

    const inputData = buildInputData();

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

    if (inputData.password || inputData['confirm-password']) {
      const isPasswordValid = validator.validatePassword(
        inputData.password,
        inputData['confirm-password']
      );
      if (!isPasswordValid.valid) {
        showFieldError('password', isPasswordValid.message);
        showFieldError('confirm-password', isPasswordValid.message);
        isValid = false;
      }
    }

    return isValid;
  }

  async function submitProfileData(currentPassword = '') {
    if (!hasChanges()) {
      showFieldError('form', 'No changes detected.');
      return;
    }

    if (hasSensitiveChanges() && !currentPassword) {
      showFieldError(
        'form',
        'Current password is required to update sensitive information.'
      );
      return;
    }

    const inputData = buildInputData();
    inputData['current-password'] = currentPassword;
    delete inputData['confirm-password'];
    if (inputData['avatar'] === '') {
      inputData['avatar'] = null;
    }

    const loadingText = 'Updating Profile...';
    setButtonLoading(submitButton, loadingText);

    try {
      const res = await api.post(endpoints.PROFILE, inputData);

      if (res.data && res.data.emailVerificationRequired) {
        window.location.href = '/email-sent?action=verify-email';
        return;
      }

      if (res.data.avatarHtml) {
        const profileNavLinks = document.querySelectorAll(
          '.nav-link[href="/profile"]'
        );
        profileNavLinks.forEach((profileNavLink) => {
          profileNavLink.innerHTML = res.data.avatarHtml;
        });
      }

      showToast(ToastType.SUCCESS, ToastMessage['profile-update-success']);

      const passwordFields = ['password', 'confirm-password'];
      passwordFields.forEach((field) => {
        const input = document.getElementById(field);
        if (input) {
          input.value = '';
        }
      });

      initialValues.clear();
      Array.from(form.elements)
        .filter((el) => el.name)
        .forEach((el) => {
          initialValues.set(el.name, currentValue(el));
        });
      clearFormErrors();
    } catch (error) {
      showServerFeedback(error.data?.error || {});
      if (error.data?.error?.general) {
        showFieldError('form', error.data.error.general);
      }
    }
    clearButtonLoading(submitButton);
  }

  function updateAvatarPreview(target) {
    if (!target.matches('.avatar-selection-radio')) {
      return;
    }

    const preview = document.getElementById('avatar-preview');
    const selecteAvatarEl = target.parentElement.querySelector(
      '.avatar, .letter-avatar'
    );
    if (!selecteAvatarEl) {
      return;
    }

    const copy = selecteAvatarEl.cloneNode(true);
    copy.classList.remove('avatar-medium');
    copy.classList.add('avatar-large');
    preview.replaceChildren(copy);
  }

  async function loadAvatarOptions(previousButton, nextButton, direction) {
    const listEl = document.querySelector('.avatar-selection-list');
    if (!listEl) {
      return;
    }
    const currentPage = listEl.dataset.page;
    const totalPages = listEl.dataset.totalPages;
    const nextPage =
      direction === 'next'
        ? parseInt(currentPage) + 1
        : parseInt(currentPage) - 1;

    if (nextPage < 1 || nextPage > totalPages) {
      return;
    }

    const url = `${endpoints.AVATAR_OPTIONS}?page=${nextPage}`;
    try {
      const response = await api.get(url);
      if (!response.ok) {
        throw new Error('Failed to load more avatar options');
      }

      const avatarOptionsHTML = response.data.html;
      const newList = document
        .createRange()
        .createContextualFragment(avatarOptionsHTML);
      listEl.replaceWith(newList);

      if (response.data.totalPages > nextPage) {
        nextButton.classList.remove('invisible');
      } else {
        nextButton.classList.add('invisible');
      }
      if (nextPage === 1) {
        previousButton.classList.add('invisible');
      } else {
        previousButton.classList.remove('invisible');
      }
    } catch (error) {
      showToast(
        ToastType.ERROR,
        error.message || 'Failed to load more avatar options'
      );
    }
  }

  initPasswordToggles();
  form.addEventListener('input', () => clearFormErrors());

  const dialogElement = document.getElementById('password-confirmation-dialog');
  const currentPasswordInput = document.getElementById('current-password');
  let currentPassword = '';
  document
    .getElementById('confirm-current-password-button')
    ?.addEventListener('click', (event) => {
      event.preventDefault();
      currentPassword = currentPasswordInput ? currentPasswordInput.value : '';
      dialogElement.close('confirm');
    });

  document
    .getElementById('cancel-current-password-button')
    ?.addEventListener('click', (event) => {
      event.preventDefault();
      dialogElement.close('cancel');
    });

  dialogElement.addEventListener('close', () => {
    if (currentPasswordInput.value) {
      currentPasswordInput.value = '';
    }
    if (dialogElement.returnValue === 'confirm') {
      submitProfileData(currentPassword);
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const isValid = validateInput();
    if (!isValid) {
      return;
    }

    if (hasSensitiveChanges()) {
      dialogElement.showModal();
      return;
    }

    submitProfileData();
  });

  const container = document.querySelector('.avatar-selection');
  if (container) {
    container.addEventListener('change', (event) => {
      updateAvatarPreview(event.target);
    });

    const previousButton = container.querySelector('.previous-page-button');
    const nextButton = container.querySelector('.next-page-button');

    previousButton?.addEventListener('click', async (event) => {
      event.preventDefault();
      await loadAvatarOptions(previousButton, nextButton, 'previous');
    });

    nextButton?.addEventListener('click', async (event) => {
      event.preventDefault();
      await loadAvatarOptions(previousButton, nextButton, 'next');
    });
  }
}

init();
