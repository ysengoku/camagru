import { api, endpoints } from '../api';
import { showToast, ToastType } from '../toast';

const PAGE_SIZE = 10;

async function loadMorePhotos(currentCount, showMoreButton) {
  try {
    const url = `${endpoints.MY_PHOTOS}?offset=${currentCount}&limit=${PAGE_SIZE}`;
    const response = await api.get(url);

    if (!response.ok) {
      throw new Error('Failed to load more photos');
    }

    const newPhotosHTML = response.data.html;
    showMoreButton.insertAdjacentHTML('beforebegin', newPhotosHTML);

    if (response.data.count <= currentCount + PAGE_SIZE) {
      showMoreButton.classList.add('display-none');
    }
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to load more photos');
  }
}

function init() {
  console.log('Initializing gallery.js');
  const galleryItemsEl = document.getElementById('gallery-items');
  if (!galleryItemsEl) {
    return;
  }

  const showMoreButton = document.getElementById('show-more-photos-button');
  if (showMoreButton) {
    showMoreButton.addEventListener('click', () => {
      const currentCount = galleryItemsEl.children.length - 1;
      loadMorePhotos(currentCount, showMoreButton);
    });
  }
}

init();
