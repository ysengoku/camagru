<?php

require_once __DIR__ . '/../components/avatar.php';

if (!function_exists('render_avatar_selection')) {
    function render_avatar_selection($user): string {
        $posts = Post::findByUserId($user->id);

        if (!$posts) {
            return <<<HTML
            <p class="color-gray-500 font-size-3 my-4">
                No avatars available. Upload images in the Studio to select as your avatar.</p>
            HTML;
        }
        
        $avatarsHtml = '';

        foreach ($posts as $post) {
            $checked = $post->image_path === $user->avatar ? 'checked' : '';
            $imageHtml = render_avatar('', 'medium', $post->image_path);

            $avatarsHtml .= <<<HTML
            <label class="avatar-selection-option">
                <input type="radio" name="avatar" value="{$post->image_path}" class="avatar-selection-radio" {$checked}>
                {$imageHtml}
            </label>
            HTML;
        }

        return <<<HTML
        <div class="avatar-selection mt-2">
            <p class="color-gray-500 font-size-3 my-2">Select an avatar from your uploaded images:</p>
            <div class="avatar-selection-list">
                {$avatarsHtml}
            </div>
        </div>
        HTML;
    }
}
