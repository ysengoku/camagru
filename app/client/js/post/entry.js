// Post page entry point
import '../main.js'; // Common dependencies

import { initComments } from '../post/comments.js';
import { initLikeButton } from '../post/like.js';
import { adjustPostViewHeight } from './postView.js';

adjustPostViewHeight();
initComments();
initLikeButton();
