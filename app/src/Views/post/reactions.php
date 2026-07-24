<?php
    require_once __DIR__ . '/../components/icon.php';

if (!function_exists('render_post_reactions')) {
    function render_post_reactions(int $postId, int $likesCount, bool $is_liked_by_current_user, int $commentsCount, bool $interactive = false): string {
        $likeTag = $interactive ? 'button' : 'div';
        $likeButtonId = $interactive ? 'id="like-button"' : '';
        $likeTypeAttr = $interactive ? 'type="button"' : '';
        $likeDataAttr = 'data-like="' . htmlspecialchars((string)$postId, ENT_QUOTES) . '"';

        $heartIcon = $is_liked_by_current_user ? render_icon('heartfill') : render_icon('heart');
        $heartIconClass = $is_liked_by_current_user ? 'liked' : '';

        $commentIcon = render_icon('bubble');
        $commentDataAttr = 'data-comment="' . htmlspecialchars((string)$postId, ENT_QUOTES) . '"';

        $likesCountHtml = $likesCount > 0 ? htmlspecialchars((string)$likesCount, ENT_QUOTES) : '';
        $commentsCountHtml = $commentsCount > 0 ? htmlspecialchars((string)$commentsCount, ENT_QUOTES) : '';

        return <<<HTML
        <div class="flex items-center px-4 mt-2 gap-4">
            <div class="post-reactions">
                <{$likeTag}
                  {$likeTypeAttr}
                  {$likeButtonId}
                  class="flex align-center gap-1 border-none bg-transparent p-0 {$heartIconClass}"
                  {$likeDataAttr}
                >
                    {$heartIcon}
                    <p>
                        {$likesCountHtml}
                    </p>
                </{$likeTag}    >
            </div>

            <div class="post-reactions">
                <div class="flex align-center gap-1 border-none bg-transparent p-0 color-gray-500" {$commentDataAttr}>
                    {$commentIcon}
                        <p>
                            {$commentsCountHtml}
                        </p>
                </div>
            </div>
        </div>
        HTML;
    }
}
?>
