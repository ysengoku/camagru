import { showToast, ToastType, ToastMessage } from '../toast.js';

let isOpeningPostView = false;
let previousUrl;

async function openPostViewOverlay(postId) {
  if (isOpeningPostView) {
    return;
  }
  isOpeningPostView = true;

  try {
    const postViewOverlay = document.createElement('div');
    postViewOverlay.classList.add('post-view-overlay');

    const headerHeight = document.querySelector('header')?.offsetHeight || 0;
    const overlayHeight = window.innerHeight - headerHeight;
    postViewOverlay.style.top = `${headerHeight}px`;
    postViewOverlay.style.height = `${overlayHeight}px`;

    const res = await fetch(`/post?postId=${postId}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) {
      showToast(ToastType.ERROR, ToastMessage['post-not-found']);
      return;
    }
    const postViewHTML = await res.text();
    postViewOverlay.innerHTML = postViewHTML;

    document.querySelector('main')?.appendChild(postViewOverlay);

    const closeButton = document.getElementById('close-post-view-button');
    if (closeButton) {
      closeButton.classList.remove('display-none');
      closeButton.addEventListener('click', () => closePostViewOverlay());
    }

    previousUrl = window.location.href;
    history.pushState({ postId }, '', `/post?postId=${postId}`);
    document.body.style.overflow = 'hidden';
  } finally {
    isOpeningPostView = false;
  }
}

function removePostViewOverlay() {
  const postViewOverlay = document.querySelector('.post-view-overlay');
  if (postViewOverlay) {
    document.querySelector('main')?.removeChild(postViewOverlay);
    document.body.style.overflow = '';
  }
}

function closePostViewOverlay() {
  removePostViewOverlay();
  history.replaceState(null, '', previousUrl);
}

function init() {
  const containerEl = document.querySelector('.container');

  if (!containerEl) {
    return;
  }
  containerEl.addEventListener('click', async (event) => {
    if (event.ctrlKey || event.metaKey || event.button !== 0) {
      return;
    }
    const postEl = event.target.closest('.post-preview');
    if (!postEl) {
      return;
    }
    event.preventDefault();

    const postId = postEl.dataset.postId;
    await openPostViewOverlay(postId);
  });

  window.addEventListener('popstate', () => {
    if (document.querySelector('.post-view-overlay')) {
      removePostViewOverlay();
      history.pushState(null, '', location.href);
    }
  });
}

init();
