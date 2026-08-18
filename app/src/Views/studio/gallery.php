<?php
    require_once __DIR__ . '/galleryItem.php';

if (!function_exists('render_studio_gallery')) {
    function render_studio_gallery(array $posts, int $postCount): string {
        $galleryItemsHtml = '';
        $showMoreButtonHtml = '';

        if (empty($posts)) {
            $galleryItemsHtml = <<<HTML
            <div class="color-primary-700 mx-4 px-4 mt-4 gap-4 text-center no-posts">
                <p class="py-2">Nothing here yet...</p>
                <p>Capture your first photo now!</p>
            </div>
            HTML;
        } else {
            foreach ($posts as $post) {
                $galleryItemsHtml .= render_gallery_item($post['id'], $post['image_path']);
            }
            if (count($posts) < $postCount) {
                $showMoreButtonHtml = '<button id="show-more-photos-button" class="button-no-border my-4">Show More</button>';
            }
        }

        return <<<HTML
        <div id="studio-gallery" class="bg-frosted-glass-200">
            <p class="text-center font-size-5 my-4 pt-4 color-primary-500">Your Creations</p>
            <div id="gallery-items">
                $galleryItemsHtml
                $showMoreButtonHtml
            </div>
        </div>
        HTML;
    }
}
?>
