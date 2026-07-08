<?php
require_once __DIR__ . '/../components/icon.php';
require_once __DIR__ . '/../components/avatar.php';
require_once __DIR__ . '/../feed/postHeader.php';
require_once __DIR__ . '/../feed/postReactions.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/commentForm.php';

/**
 * @var PostData $postData
 */
$postHeaderHtml = render_post_header($postData->author_name, $postData->author_avatar, $postData->created_at, 'large');
$postReactionsHtml = render_post_reactions($postData->id, $postData->likes_count, $postData->comments_count);
?>

<div class="flex justify-center bg-frosted-glass-200 p-4">
    <div class="post-view">
        <?= $postHeaderHtml ?>
        <div class="post-view-image">
            <img src="<?= htmlspecialchars($postData->image_path) ?>" alt="Post Image" />
        </div>
        <?= $postReactionsHtml ?>
        <div class="post-view-comments mt-4 pt-4">
            <?= render_comments($postData->comments) ?>
        </div>
        <?= render_comment_form($postData->id) ?>
    </div>
</div>
