<?php
/**
 * Render an avatar component.
 * 
 * Displays either an avatar image or a letter avatar based on whether $avatarPath is provided.
 */
if (!function_exists('render_avatar')) {
    function render_avatar(string $displayName, string $size = 'medium', ?string $avatarPath = null): string {
        $sizeHtml = htmlspecialchars($size, ENT_QUOTES);

        if ($avatarPath === null) {
            // Letter avatar
            $letter = htmlspecialchars(strtoupper(substr($displayName, 0, 1)), ENT_QUOTES);
            return <<<HTML
            <span class="letter-avatar avatar-{$sizeHtml}">{$letter}</span>
            HTML;
        }
        
        // Image avatar
        $pathHtml = htmlspecialchars($avatarPath, ENT_QUOTES);
        $displayNameHtml = htmlspecialchars($displayName, ENT_QUOTES);
        return <<<HTML
        <img class="avatar avatar-{$sizeHtml}" src="{$pathHtml}" alt="{$displayNameHtml}'s avatar">
        HTML;
    }
}