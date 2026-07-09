<?php
if (!function_exists('render_gallery_item')) {
    function render_gallery_item(string $postId, string $imagePath): string {
        $postIdHtml = htmlspecialchars($postId, ENT_QUOTES);
        $imagePathHtml = htmlspecialchars($imagePath, ENT_QUOTES);

        return <<<HTML
        <div class="gallery-item px-4">
            <a href="/post?postId={$postIdHtml}">
                <img
                  src="{$imagePathHtml}"
                  alt="Photo"
                  class="gallery-photo w-100"
                />
            </a>
        </div>
        HTML;
    }
}
?>
