import { api, endpoints } from '../api.js';
import { showToast, ToastType } from '../toast.js';

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
  } catch (error) {
    showToast(ToastType.ERROR, error.message || 'Failed to delete comment');
  }
}

export function initCommentForm() {
  const commentsContainer = document.querySelector('.post-comments');
  const commentForm = document.querySelector('.comment-form');
  if (!commentsContainer || !commentForm) {
    return;
  }

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
    const postId = parseInt(commentForm.dataset.postId, 10);
    const content = commentForm.querySelector('textarea').value;
    await addComment(postId, content, commentsContainer);
    commentForm.querySelector('textarea').value = '';
  });
}
