<?php
    require_once __DIR__ . '/../components/avatar.php';

if (!function_exists('render_post_header')) {
    function render_post_header(string $authorName, ?string $authorAvatar, string $createdAt, string $size = 'medium'): string {
        $timestamp = strtotime($createdAt);
        $formattedDate = $timestamp !== false
            ? htmlspecialchars(date('F j, Y', $timestamp), ENT_QUOTES)
            : htmlspecialchars($createdAt, ENT_QUOTES);
        $avatar = render_avatar($authorName, $size, $authorAvatar);
        $authorNameHtml = htmlspecialchars($authorName, ENT_QUOTES);
        $authorNameSize = $size === 'medium' ? 'font-size-4' : 'font-size-5';
            
        return <<<HTML
        <div class="flex align-center mb-2 px-4 gap-4">
            {$avatar}
            <div class="flex-col gap-1">
                <p class="author-name font-bold {$authorNameSize} color-gray-600">
                    {$authorNameHtml}
                </p>
                <p class="font-size-3 color-gray-500">
                    {$formattedDate}
                </p>
            </div>
        </div>
        HTML;
    }
}
?>
