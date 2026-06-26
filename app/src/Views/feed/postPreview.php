<?php
require_once __DIR__ . '/postHeader.php';
require_once __DIR__ . '/postReactions.php';
?>

<?php
if (!function_exists('render_post_preview')) {
    function render_post_preview(PostData $post): string {
        $header    = render_post_header($post->author_name, $post->author_avatar, $post->created_at);
        $reactions = render_post_reactions($post->id, $post->likes_count, count($post->comments));
        $imagePath = htmlspecialchars($post->image_path, ENT_QUOTES);

        return <<<HTML
            <div class="post-preview-card">
                {$header}
                <img src="{$imagePath}" alt="Post Image" class="w-full h-auto object-cover rounded">
                <div class="flex flex-col">
                    {$reactions}
                </div>
            </div>
            HTML;
    }
}
?>
