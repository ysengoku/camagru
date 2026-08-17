import { api, endpoints } from '../api.js';
import { showToast, ToastType } from '../toast.js';
import { initLikeButton } from '../post/like.js';

const PAGE_SIZE = 10;

async function loadMorePhotos(feedContainer, observer) {
  try {
    const offset = feedContainer.children.length;
    const url = `${endpoints.PHOTOS}?offset=${offset}&limit=${PAGE_SIZE}`;
    const response = await api.get(url);
    if (!response.ok) {
      throw new Error('Failed to load more photos');
    }

    const newPhotosHTML = response.data.html;
    feedContainer.insertAdjacentHTML('beforeend', newPhotosHTML);

    if (response.data.count <= feedContainer.children.length) {
      observer.disconnect();
    }
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to load more photos');
  }
}

function init() {
  const feedContainer = document.getElementById('feed-container');
  const observerEl = document.getElementById('observer');
  if (!feedContainer || !observerEl) {
    return;
  }

  const options = {
    root: null,
    rootMargin: '16px',
    threshold: 1.0,
  };

  let isLoading = false;

  const observer = new IntersectionObserver((entries) => {
    const entry = entries[entries.length - 1];
    if (!entry.isIntersecting || isLoading) {
      return;
    }
    isLoading = true;
    loadMorePhotos(feedContainer, observer).finally(() => {
      isLoading = false;
    });
  }, options);
  observer.observe(observerEl);

  initLikeButton(document.querySelector('.container'));
}

init();
