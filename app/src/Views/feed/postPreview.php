<?php
require_once __DIR__ . '/../post/postHeader.php';
require_once __DIR__ . '/../post/reactions.php';
?>

<?php
if (!function_exists('render_post_preview')) {
    function render_post_preview(PostData $post): string {
        $header    = render_post_header($post->author_name, $post->author_avatar, $post->created_at);
        $reactions = render_post_reactions($post->id, $post->likes_count, $post->comments_count);
        $imagePath = htmlspecialchars($post->image_path, ENT_QUOTES);

        return <<<HTML
            <a href="/post?postId={$post->id}" class="text-decoration-none color-gray-700">
                <div class="post-preview-card bg-frosted-glass-200 gap-2">
                    {$header}
                    <img src="{$imagePath}" alt="Post Image" class="w-full h-auto object-cover rounded">
                    <div class="flex flex-col">
                        {$reactions}
                    </div>
                </div>
            </a>
            HTML;
    }
}
?>
