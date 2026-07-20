import { api, endpoints } from '../api.js';
import { showToast, ToastType } from '../toast.js';

const PAGE_SIZE = 10;

async function loadMorePhotos(offset, feedContainer, loadMoreButton) {
  try {
    const url = `${endpoints.PHOTOS}?offset=${offset}&limit=${PAGE_SIZE}`;
    const response = await api.get(url);

    if (!response.ok) {
      throw new Error('Failed to load more photos');
    }

    const newPhotosHTML = response.data.html;
    const buttonContainer = loadMoreButton.parentElement;
    buttonContainer.insertAdjacentHTML('beforebegin', newPhotosHTML);

    if (response.data.count <= feedContainer.children.length) {
      buttonContainer.classList.add('display-none');
    }
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to load more photos');
  }
}

function init() {
  const feedContainer = document.getElementById('feed-container');
  if (!feedContainer) {
    return;
  }

  const loadMoreButton = document.getElementById('load-more-posts-button');
  console.log('Load more button:', loadMoreButton);
  if (loadMoreButton) {
    loadMoreButton.addEventListener('click', async () => {
      console.log('Load more button clicked');
      const currentCount = feedContainer.children.length - 1;
      await loadMorePhotos(currentCount, feedContainer, loadMoreButton);
    });
  }
}

init();
