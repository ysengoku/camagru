<?php
/**
 * @var User|null $user
 */
?>

<?php
    require_once __DIR__ . '/components/navLink.php';
?>

<header >
    <div class="flex align-center px-4 pt-1 gap-4 relative">
        <a href="/" class="logo-link">
            <img src="/assets/img/logo.png" alt="Camagru Logo" class="logo">
        </a>
        <div class="navbar flex justify-between absolute bottom-0 left-0 right-0 px-4">
            <?php if ($user) : ?>
                <?php echo render_nav_link($user); ?>
            <?php endif; ?>
            <nav class="flex align-center ms-auto">
                <?php if ($user) : ?>
                    <button type="button" id="logout-button" class="nav-action pe-4 border-none bg-transparent p-0 font-size-4 color-gray-500 cursor-pointer">
                    Logout
                    </button>
                <?php else : ?>
                    <a href="/login" class="nav-action pe-4 pb-4 font-size-4 color-gray-500 cursor-pointer text-decoration-none">
                        Login
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
