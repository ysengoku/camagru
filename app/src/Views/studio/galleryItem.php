<?php

require_once __DIR__ . '/../components/icon.php';

if (!function_exists('render_gallery_item')) {
    function render_gallery_item(string $postId, string $imagePath): string {
        $postIdHtml = htmlspecialchars($postId, ENT_QUOTES);
        $imagePathHtml = htmlspecialchars($imagePath, ENT_QUOTES);
        $moreIconHtml = render_icon('more');

        return <<<HTML
        <div class="gallery-item px-4 mx-2 relative">
            <div class="gallery-item-dropdown">
                <button class="gallery-item-menu-button" type="button">
                    {$moreIconHtml}
                </button>
                <ul class="gallery-item-dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li class="gallery-item-action color-primary-100" data-action="download" data-post-id="{$postIdHtml}">
                        Download
                    </li>
                    <li class="gallery-item-action color-danger" data-action="delete" data-post-id="{$postIdHtml}">
                        Delete
                    </li>
                </ul>
            </div>
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
