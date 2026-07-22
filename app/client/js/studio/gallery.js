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

async function deletePhoto(postId, galleryItemEl) {
  try {
    const url = `${endpoints.PHOTOS}?postId=${postId}`;
    const response = await api.delete(url);
    if (!response.ok) {
      throw new Error(response.data.message || 'Failed to delete photo');
    }

    galleryItemEl?.remove();
    showToast(ToastType.SUCCESS, 'Photo deleted successfully');
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to delete photo');
  }
}

function init() {
  const galleryItemsEl = document.getElementById('gallery-items');
  if (!galleryItemsEl) {
    return;
  }

  galleryItemsEl.addEventListener('click', (event) => {
    const dropdown = event.target.closest('.gallery-item-dropdown');
    if (dropdown) {
      dropdown.classList.toggle('open');
    }

    const deleteButton = event.target.closest('.delete-button');
    if (!deleteButton) {
      return;
    }
    const postId = deleteButton.dataset.postId;
    const galleryItemEl = deleteButton.closest('.gallery-item');
    deletePhoto(postId, galleryItemEl);
  });

  const showMoreButton = document.getElementById('show-more-photos-button');
  if (showMoreButton) {
    showMoreButton.addEventListener('click', () => {
      const currentCount = galleryItemsEl.children.length - 1;
      loadMorePhotos(currentCount, showMoreButton);
    });
  }
}

init();
