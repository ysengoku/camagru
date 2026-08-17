import { showToast, ToastType, ToastMessage } from '../toast.js';
import { adjustPostViewHeight } from '../post/postView.js';
import { initComments } from '../post/comments.js';

let isOpeningPostView = false;
let previousUrl;

async function openPostViewOverlay(postId) {
  if (isOpeningPostView) {
    return;
  }
  isOpeningPostView = true;

  try {
    const res = await fetch(`/post?postId=${postId}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) {
      showToast(ToastType.ERROR, ToastMessage['post-not-found']);
      return;
    }

    const postViewHTML = await res.text();
    document
      .querySelector('main')
      ?.insertAdjacentHTML('beforeend', postViewHTML);

    adjustPostViewHeight();

    const closeButton = document.getElementById('back-to-feed-button');
    if (closeButton) {
      closeButton.classList.remove('display-none');
      closeButton.addEventListener('click', () => closePostViewOverlay());
    }

    previousUrl = window.location.href;
    history.pushState({ postId }, '', `/post?postId=${postId}`);
    document.body.style.overflow = 'hidden';
    initComments();
  } finally {
    isOpeningPostView = false;
  }
}

function removePostViewOverlay() {
  const postViewOverlay = document.querySelector('.post-view-overlay');
  if (postViewOverlay) {
    postViewOverlay.remove();
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
    if (event.target.closest('[data-like]')) {
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
