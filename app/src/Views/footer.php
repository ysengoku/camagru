<?php
/**
 * @var User|null $user
 */
require_once __DIR__ . '/components/navLink.php';

$footerBgColor = $user ? 'bg-frosted-glass-300' : 'bg-transparent';
?>

<footer class="<?= $footerBgColor ?>">
    <?php if ($user) : ?>
        <div class="footer-nav">
            <?php echo render_nav_link($user); ?>
        </div>
    <?php endif; ?>
    <div class="footer-copyright color-primary-600 flex align-center justify-center">
        <p class="font-bold">&copy; 2026 Camagru. All rights reserved.</p>
    </div>
</footer>