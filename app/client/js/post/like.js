import { api, endpoints } from '../api.js';
import { showToast, ToastType } from '../toast.js';

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
  showToast('Failed to update like status', ToastType.ERROR);
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

export function initLikeButton() {
  const likeButton = document.getElementById('like-button');

  likeButton?.addEventListener('click', async () => {
    const postId = likeButton.dataset.like;
    const isLiked = likeButton.classList.contains('liked');
    await toggleLike(postId, isLiked);
  });
}
