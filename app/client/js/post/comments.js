import { api, endpoints } from '../api.js';
import { showToast, ToastType } from '../toast.js';

const PAGE_SIZE = 10;

async function addComment(postId, content, commentsContainer) {
  try {
    const response = await api.post(endpoints.COMMENTS, { postId, content });
    if (!response.ok) {
      throw new Error('Failed to add comment');
    }

    const newCommentHTML = response.data.html;
    commentsContainer.appendChild(
      document.createRange().createContextualFragment(newCommentHTML)
    );
    updateCommentCount(response.data.postId, response.data.commentCount);
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to add comment');
  }
}

async function deleteComment(commentId) {
  const url = `${endpoints.COMMENTS}?commentId=${commentId}`;

  try {
    const response = await api.delete(url);
    if (!response.ok) {
      throw new Error('Failed to delete comment');
    }

    const commentEl = document.querySelector(
      `.comment[data-comment-id="${commentId}"]`
    );
    if (commentEl) {
      commentEl.remove();
    }
    updateCommentCount(response.data.postId, response.data.commentCount);
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to delete comment');
  }
}

function updateCommentCount(postId, newCount) {
  const commentEl = document.querySelectorAll(`[data-comment="${postId}"]`);
  commentEl.forEach((el) => {
    el.querySelector('p').textContent = newCount;
  });
}

async function loadMoreComments(postId, curentCount, commentsContainer) {
  try {
    const url = `${endpoints.COMMENTS}?postId=${postId}&offset=${curentCount}&limit=${PAGE_SIZE}`;
    const response = await api.get(url);

    if (!response.ok) {
      throw new Error('Failed to load more comments');
    }

    const newCommentsHTML = response.data.html;
    commentsContainer.insertAdjacentHTML('beforeend', newCommentsHTML);
    updateCommentCount(postId, response.data.count);

    if (response.data.count <= curentCount + PAGE_SIZE) {
      const showMoreButton = document.getElementById(
        'show-more-comments-button'
      );
      if (showMoreButton) {
        showMoreButton.classList.add('display-none');
      }
    }
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to load more comments');
  }
}

export function initComments() {
  const commentsContainer = document.querySelector('.post-comments');
  const commentForm = document.querySelector('.comment-form');
  if (!commentsContainer || !commentForm) {
    return;
  }

  const postId = parseInt(commentForm.dataset.postId, 10);

  commentsContainer.addEventListener('click', async (event) => {
    const deleteButton = event.target.closest('.delete-comment-button');
    if (!deleteButton) {
      return;
    }
    const commentElement = deleteButton.closest('.comment');
    if (!commentElement) {
      return;
    }
    const commentId = commentElement.dataset.commentId;
    if (!commentId) {
      return;
    }

    await deleteComment(commentId);
  });

  commentForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const content = commentForm.querySelector('textarea').value;
    await addComment(postId, content, commentsContainer);
    commentForm.querySelector('textarea').value = '';
  });

  const showMoreButton = document.getElementById('show-more-comments-button');
  if (showMoreButton) {
    showMoreButton.addEventListener('click', async () => {
      console.log('Show more comments button clicked');
      const currentCount =
        commentsContainer.querySelectorAll('.comment').length;
      await loadMoreComments(postId, currentCount, commentsContainer);
    });
  }
}
