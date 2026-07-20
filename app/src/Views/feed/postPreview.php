<?php
require_once __DIR__ . '/../post/postHeader.php';
require_once __DIR__ . '/../post/reactions.php';

if (!function_exists('render_post_preview')) {
    function render_post_preview(PostData $post): string {
        $header    = render_post_header($post->author_name, $post->author_avatar, $post->created_at);
        $reactions = render_post_reactions($post->id, $post->likes_count, $post->is_liked_by_current_user, $post->comments_count);
        $imagePath = htmlspecialchars($post->image_path, ENT_QUOTES);

        return <<<HTML
            <a href="/post?postId={$post->id}" class="post-preview text-decoration-none color-gray-700" data-post-id="{$post->id}">
                <div class="post-preview-card bg-frosted-glass-200 gap-2 h-100 rounded">
                    <div class="mt-4">
                        {$header}
                    </div>
                    <div class="post-preview-image bg-transparent flex-1 flex-col justify-center items-center">
                        <img src="{$imagePath}" alt="Post Image">
                    </div>
                    <div class="flex flex-col mb-4">
                        {$reactions}
                    </div>
                </div>
            </a>
            HTML;
    }
}
?>
