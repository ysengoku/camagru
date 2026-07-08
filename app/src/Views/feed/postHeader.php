<?php
    require_once __DIR__ . '/../components/avatar.php';
?>

<?php
if (!function_exists('render_post_header')) {
    function render_post_header(string $authorName, ?string $authorAvatar, string $createdAt, string $avatarSize = 'small'): string {
        $timestamp = strtotime($createdAt);
        $formattedDate = $timestamp !== false
            ? htmlspecialchars(date('F j, Y', $timestamp), ENT_QUOTES)
            : htmlspecialchars($createdAt, ENT_QUOTES);
        $avatar = render_avatar($authorName, $avatarSize, $authorAvatar);
        $authorNameHtml = htmlspecialchars($authorName, ENT_QUOTES);
            
        return <<<HTML
        <div class="flex flex-col mb-2 px-4">
            <div class="flex align-center my-1 gap-2">
                {$avatar}
                <p class="font-bold">{$authorNameHtml}</p>
            </div>
            <p class="font-size-3 text-gray-500 my-1 ps-1">
                {$formattedDate}
            </p>
        </div>
        HTML;
    }
}
?>
