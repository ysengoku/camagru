<?php
require_once __DIR__ . '/../components/icon.php';
require_once __DIR__ . '/postHeader.php';
require_once __DIR__ . '/reactions.php';
require_once __DIR__ . '/comments.php';
require_once __DIR__ . '/commentForm.php';

/**
 * @var PostData $postData
 * @var User|null $user
 * @var bool $isOverlay
 */
$backButtonIcon = render_icon('arrowleft');
$backButtonHtml = $isOverlay
    ? <<<HTML
        <button id="back-to-feed-button">
            {$backButtonIcon}
        </button>
    HTML
    : <<<HTML
        <a href="/" id="back-to-feed-button">
            {$backButtonIcon}
        </a>
    HTML;

$postHeaderHtml = render_post_header($postData->author_name, $postData->author_avatar, $postData->created_at, 'large');
$postReactionsHtml = render_post_reactions($postData->id, $postData->likes_count, $postData->is_liked_by_current_user, $postData->comments_count, $user !== null);
?>

<div class="post-view-overlay">
    <div class="post-view-container">
        <div class="post-view bg-frosted-glass-200 relative">
            <?= $backButtonHtml ?>
            <div class="post-view-image">
            <img src="<?= htmlspecialchars($postData->image_path) ?>" alt="PostImage" />
            </div>
            <div class="post-view-details">
                <?= $postHeaderHtml ?>
                <?= $postReactionsHtml ?>
                <hr class="my-2 border-gray-300 w-100" />
                <?= render_comments($postData->comments, $user ? $user->id : null) ?>
                <?php if (isset($user) && $user !== null) : ?>
                    <hr class="my-2 border-gray-300 w-100" />
                    <?= render_comment_form($postData->id) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
