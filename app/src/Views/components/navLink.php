<?php
    require_once __DIR__ . '/icon.php';
    require_once __DIR__ . '/avatar.php';
?>

<?php
    const NAV_LINKS = [
        ['href' => '/', 'icon' => 'gallery', 'label' => 'Gallery'],
        ['href' => '/studio', 'icon' => 'camera', 'label' => 'Studio'],
        ['href' => '/profile', 'icon' => 'profile', 'label' => 'Profile'],
    ];
    ?>

<?php
if (!function_exists('render_nav_link')) {
    function render_nav_link(): string {
        $current = $_SERVER['REQUEST_URI'] ?? '/';
        $navLinksHtml = '';
        foreach (NAV_LINKS as $link) {
            $active = $current === $link['href'] ? 'nav-link-active' : '';
            $iconHtml = render_icon($link['icon']);
            $navLinksHtml .= <<<HTML
            <div class="nav-link-container {$active}">
                <a href="{$link['href']}" class="px-1 nav-link">
                    {$iconHtml}
                </a>
            </div>
            HTML;
        }

        return <<<HTML
        <nav class="flex flex-1 justify-center align-start">
            {$navLinksHtml}
        </nav>
        HTML;
    }
}
?>
