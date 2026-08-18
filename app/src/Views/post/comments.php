<?php

require_once __DIR__ . '/../components/avatar.php';
require_once __DIR__ . '/../components/icon.php';

if (!function_exists('render_comment')) {
    function render_comment(PostCommentData $comment, ?int $userId): string {
        $commentId = htmlspecialchars((string)$comment->id, ENT_QUOTES);
        $avatarHtml = render_avatar($comment->author_name, 'medium',$comment->author_avatar);
        $authorName = htmlspecialchars($comment->author_name);
        $timestamp = strtotime($comment->created_at);
        $createdAt = $timestamp !== false
            ? htmlspecialchars(date('M', $timestamp) . date(' j, Y, g:i A', $timestamp), ENT_QUOTES)
            : htmlspecialchars($comment->created_at, ENT_QUOTES);
        $content = htmlspecialchars($comment->content);

        $deleteButtonHtml = '';
        if ($userId !== null && $userId === $comment->author_id) {
            $closeIcon = render_icon('close');
            $deleteButtonHtml = <<<HTML
                <button class="delete-comment-button" data-comment-id="{$commentId}">
                    {$closeIcon}
                </button>
            HTML;
        }

        return <<<HTML
            <div class="comment flex-col gap-1 mb-4 p-2" data-comment-id="{$commentId}">
                <div class="flex align-center mb-1 gap-2">
                    {$avatarHtml}
                    <div class="flex align-end gap-2">
                        <span class="color-gray-600 font-bold font-size-4 author-name">     
                            {$authorName}
                        </span>
                        <span class="color-gray-500 font-size-3">
                            {$createdAt}
                        </span>
                    </div>
                </div>
                <div class="flex align-center justify-between gap-2">
                    <p class="color-gray-700 font-size-4 comment-content">
                        {$content}
                    </p>
                    {$deleteButtonHtml}
                </div>
            </div>
        HTML;
    }
}

if (!function_exists('render_comments')) {
    function render_comments(int $postId, array $comments, ?int $userId, int $commentCount): string {
        $commentsHtml = '';
        foreach ($comments as $comment) {
            $commentsHtml .= render_comment($comment, $userId);
        }

        $renderShowMoreButton = $commentCount > count($comments);
        $showMoreButtonDisplayClass = $renderShowMoreButton ? '' : 'display-none';

        return <<<HTML
            <div class="post-comments-container">
                <div class="post-comments" data-post-id="{$postId}">
                    {$commentsHtml}
                </div>
                <button id="show-more-comments-button" class="button-no-border my-2 w-100 font-size-3 {$showMoreButtonDisplayClass}">
                    Show More Comments
                </button>
            </div>
        HTML;
    }
}
