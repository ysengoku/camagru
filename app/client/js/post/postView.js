export function adjustPostViewHeight() {
  const postViewOverlay = document.querySelector('.post-view-overlay');
  if (!postViewOverlay) {
    return;
  }

  const headerHeight = document.querySelector('header')?.offsetHeight || 0;
  const overlayHeight = window.innerHeight - headerHeight;
  postViewOverlay.style.top = `${headerHeight}px`;
  postViewOverlay.style.height = `${overlayHeight}px`;
}
