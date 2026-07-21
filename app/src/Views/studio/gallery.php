<?php
    require_once __DIR__ . '/galleryItem.php';

if (!function_exists('render_studio_gallery')) {
    function render_studio_gallery(array $posts, int $postCount): string {
        $galleryItemsHtml = '';
        $showMoreButtonHtml = '';

        if (empty($posts)) {
            $galleryItemsHtml = '<p class="text-center color-primary-700">No photos yet</p>';
        } else {
            foreach ($posts as $post) {
                $imagePathHtml = htmlspecialchars($post['image_path'], ENT_QUOTES);
                $postIdHtml = htmlspecialchars($post['id'], ENT_QUOTES);
                $galleryItemsHtml .= render_gallery_item($postIdHtml, $imagePathHtml);
            }
            if (count($posts) < $postCount) {
                $showMoreButtonHtml = '<button id="show-more-photos-button" class="button-no-border my-4">Show More</button>';
            }
        }

        return <<<HTML
        <div id="studio-gallery" class="bg-frosted-glass-200">
            <p class="text-center font-size-5 my-4 pt-4 color-primary-500 font-bold">Your Creations</p>
            <div id="gallery-items">
                $galleryItemsHtml
                $showMoreButtonHtml
            </div>
        </div>
        HTML;
    }
}
?>
