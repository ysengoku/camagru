<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<?php
if (!function_exists('render_post_reactions')) {
    function render_post_reactions(int $postId, int $likesCount, int $commentsCount): string {
        $heartIcon = render_icon('heart');
        $commentIcon = render_icon('bubble');
        $postIdHtml = htmlspecialchars((string)$postId, ENT_QUOTES);
        $likesCountHtml = $likesCount > 0 ? htmlspecialchars((string)$likesCount, ENT_QUOTES) : '';
        $commentsCountHtml = $commentsCount > 0 ? htmlspecialchars((string)$commentsCount, ENT_QUOTES) : '';

        return <<<HTML
        <div class="flex items-center mt-4 gap-4">
            <div class="post-reactions">
                <button
                  type="button"
                  class="flex align-center gap-1 border-none bg-transparent p-0 cursor-pointer hover-scale"
                  data-like="<?php echo $postId; ?>"
                >
                    {$heartIcon}
                    <p class="text-gray-500">
                        {$likesCountHtml}
                    </p>
                </button>
            </div>

            <div class="post-reactions">
                <button 
                  type="button"
                  class="flex align-center gap-1 border-none bg-transparent p-0 cursor-pointer hover-scale"
                  data-comment={$postIdHtml}
                >
                    {$commentIcon}
                        <p class="text-gray-500">
                            {$commentsCountHtml}
                        </p>
                </button>
            </div>
        </div>
        HTML;
    }
}
?>
