import { api, endpoints } from '../api.js';
import { showToast, ToastType, ToastMessage } from '../toast.js';

async function toggleLike(postId, isLiked) {
  let response;
  switch (isLiked) {
    case true:
      response = await api.delete(`${endpoints.LIKE}?postId=${postId}`);
      if (response.ok) {
        updateLikeButtonState(postId, false, response.data.likesCount);
        return;
      }
      break;
    case false:
      response = await api.post(endpoints.LIKE, { postId: postId });
      if (response.ok) {
        updateLikeButtonState(postId, true, response.data.likesCount);
        return;
      }
      break;
  }
  showToast(ToastType.ERROR, 'Failed to update like status');
}

function updateLikeButtonState(postId, isLiked, likeCount) {
  const iconName = isLiked ? 'heartfill' : 'heart';
  document.querySelectorAll(`[data-like="${postId}"]`).forEach((el) => {
    el.classList.toggle('liked', isLiked);
    el.querySelector('p').textContent = likeCount > 0 ? likeCount : '';

    const useEl = el.querySelector('use');
    useEl.setAttributeNS(
      'http://www.w3.org/1999/xlink',
      'href',
      `/assets/icons/${iconName}.svg#${iconName}-icon`
    );
    useEl.setAttribute(
      'href',
      `/assets/icons/${iconName}.svg#${iconName}-icon`
    );
  });
}

export function initLikeButton(containerEl = document) {
  containerEl.addEventListener('click', async (event) => {
    const target = event.target.closest('[data-like]');
    if (!target) {
      return;
    }

    if (target.tagName !== 'BUTTON') {
      showToast(ToastType.INFO, ToastMessage['login-required-like']);
      return;
    }

    const postId = target.dataset.like;
    const isLiked = target.classList.contains('liked');
    await toggleLike(postId, isLiked);
  });
}
