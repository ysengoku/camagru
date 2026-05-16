<?php
    require_once __DIR__ . '/galleryItem.php'
?>

<?php
if (!function_exists('render_studio_gallery')) {
    function render_studio_gallery(array $posts): string {
        $galleryItemsHtml = '';

        if (empty($posts)) {
            $galleryItemsHtml = '<p class="text-center color-primary-700">No photos yet</p>';
        } else {
            foreach ($posts as $post) {
                $imagePathHtml = htmlspecialchars($post['image_path'], ENT_QUOTES);
                $postIdHtml = htmlspecialchars($post['id'], ENT_QUOTES);
                $galleryItemsHtml .= render_gallery_item($postIdHtml, $imagePathHtml);
            }
        }

        return <<<HTML
        <div id="studio-gallery" class="studio-section">
            <h2 class="text-center text-lg font-bold mb-4">Your Gallery</h2>
            $galleryItemsHtml
        </div>
        HTML;
    }
}
?>
