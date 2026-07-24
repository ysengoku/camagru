<?php

require_once __DIR__ . '/../components/avatar.php';
require_once __DIR__ . '/../components/icon.php';

if (!function_exists('render_avatar_selection')) {
    /**
     * Renders the avatar selection section for the profile page.
     * @param list<Post> $posts An array of Post objects representing the user's uploaded images.
     * @param int $postCount The total number of posts the user has uploaded.
     * @return string The HTML for the avatar selection section.
     */
    function render_avatar_selection(string $username, ?string $userAvatar, array $posts, int $postCount): string {
        if (!$posts) {
            return <<<HTML
            <p class="color-gray-500 font-size-3 my-4">
                No avatars available. Upload images in the Studio to select as your avatar.</p>
            HTML;
        }

        $totalPages = $postCount <= 4 ? 1 : 1 + (int)ceil(($postCount - 4) / 5);
        $listHtml = render_avatar_selection_list(1, $totalPages, $username, $userAvatar, $posts);

        $previousIconHtml = render_icon('left');
        $nextIconHtml = render_icon('right');
        $previousButtonHtml = <<<HTML
        <button class="button-no-border invisible previous-page-button">
            {$previousIconHtml}
        </button>
        HTML;

        $nextButtonVisible = $totalPages > 1 ? '' : 'invisible';
        $nextButtonHtml = <<<HTML
        <button class="button-no-border {$nextButtonVisible} next-page-button">
            {$nextIconHtml}
        </button>
        HTML;

        return <<<HTML
        <div class="avatar-selection mt-2">
            <p class="color-gray-500 font-size-3 my-2">Select an avatar from your uploaded images:</p>
            <div class="flex justify-center gap-2">
                {$previousButtonHtml}
                {$listHtml}
                {$nextButtonHtml}
            </div>
            <span id="avatar-error" class="error-feedback"></span>
        </div>
        HTML;
    }
}

if (!function_exists('render_avatar_selection_list')) {
    function render_avatar_selection_list(int $pageNumber, int $totalPages, string $username, ?string $userAvatar, array $posts): string {
        $avatarsHtml = '';
        if ($pageNumber === 1) {
            $noAvatarChecked = $userAvatar === null ? 'checked' : '';
            $avatarsHtml .= render_no_avatar_option($username, $noAvatarChecked);
        }
        foreach ($posts as $post) {
            $checked = $post->image_path === $userAvatar ? 'checked' : '';
            $avatarsHtml .= render_avatar_option($username, $post, $checked);
        }

        return <<<HTML
        <div class="avatar-selection-list" data-page="{$pageNumber}" data-total-pages="{$totalPages}">
            {$avatarsHtml}
        </div>
        HTML;
    }
}

if (!function_exists('render_no_avatar_option')) {
    function render_no_avatar_option(string $username, string $checked = ''): string {
        $letterAvatarHtml = render_avatar($username, 'medium', null);

        return <<<HTML
        <label class="avatar-selection-option">
            <input type="radio" name="avatar" value="" class="avatar-selection-radio" {$checked}>
            {$letterAvatarHtml}
        </label>
        HTML;
    }
}

if (!function_exists('render_avatar_option')) {
    function render_avatar_option(string $username, Post $post, string $checked = ''): string {
        $imageHtml = render_avatar($username, 'medium', $post->image_path);

        return <<<HTML
        <label class="avatar-selection-option">
            <input type="radio" name="avatar" value="{$post->image_path}" class="avatar-selection-radio" {$checked}>
            {$imageHtml}
        </label>
        HTML;
    }
}
