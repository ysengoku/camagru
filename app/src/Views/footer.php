<?php
/**
 * @var User|null $user
 */
require_once __DIR__ . '/components/navLink.php';

$footerAuthClass = $user ? 'footer-authenticated' : '';
?>

<footer class="bg-frosted-glass-dark <?= $footerAuthClass ?>">
    <?php if ($user) : ?>
        <div class="footer-nav">
            <?php echo render_nav_link($user); ?>
        </div>
    <?php endif; ?>
    <div class="footer-copyright color-gray-100 flex align-center justify-center">
        <span>&copy; 2026 Camagru. All rights reserved.</span>
    </div>
</footer>