<header >
    <div class="flex align-center px-4 pt-1 gap-4 relative">
        <a href="/" class="logo-link">
            <img src="/assets/img/logo.png" alt="Camagru Logo" class="logo">
        </a>
        <div class="navbar flex justify-between absolute bottom-0 right-0 px-4">
            <?php if ($user) : ?>
                <?php include __DIR__ . '/components/navLink.php'; ?>
            <?php endif; ?>
            <nav class="flex gap-2">
                <?php if ($user) : ?>
                    <button type="submit" class="nav-action border-none bg-transparent p-0 font-size-4 color-gray-500 cursor-pointer" form="logout-form">
                    Logout
                    </button>
                <?php else : ?>
                    <a href="/login" class="nav-action font-size-4 color-gray-500 cursor-pointer text-decoration-none">
                        Login
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
