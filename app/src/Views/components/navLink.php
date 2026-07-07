<?php
    require_once __DIR__ . '/icon.php';
    require_once __DIR__ . '/avatar.php';
?>

<?php
    const NAV_LINKS = [
        ['href' => '/', 'icon' => 'gallery', 'label' => 'Gallery', 'authRequired' => false],
        ['href' => '/studio', 'icon' => 'camera', 'label' => 'Studio', 'authRequired' => true],
        ['href' => '/profile', 'icon' => 'profile', 'label' => 'Profile', 'authRequired' => true],
    ];

    if (!function_exists('render_nav_link')) {
        function render_nav_link(?User $user): string {
            $current = $_SERVER['REQUEST_URI'] ?? '/';
            $profileIconHtml = $user ? render_avatar($user->username, 'medium', $user->avatar) : '';

            $navLinksHtml = '';
            foreach (NAV_LINKS as $link) {
                if ($link['authRequired'] && !$user) {
                    continue;
                }
                $active = $current === $link['href'] ? 'nav-link-active' : '';
                $iconHtml = $link['href'] === '/profile' ? $profileIconHtml : render_icon($link['icon']);
                $navLinksHtml .= <<<HTML
                <div class="nav-link-container {$active}">
                    <a href="{$link['href']}" class="px-1 nav-link">
                        {$iconHtml}
                    </a>
                </div>
                HTML;
            }

            return <<<HTML
        <nav class="flex flex-1 justify-center align-end">
            {$navLinksHtml}
        </nav>
        HTML;
        }
    }
    ?>
