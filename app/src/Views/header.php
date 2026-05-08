<header>
    <div class="flex justify-between align-center p-4">
        <img src="/assets/img/logo.png" alt="Camagru Logo" class="logo">
        <nav class="flex gap-2">
            <a href="/" class="nav-link">
                <?php $name = 'house'; include __DIR__ . '/components/icon.php'; ?>

            </a>
            <?php if ($user) : ?>
                <a href="/profile" class="nav-link">
                    <?php $name = 'user'; include __DIR__ . '/components/icon.php'; ?>
                </a>    
                        <use xlink:href="/assets/icons/user.svg#user-icon"></use>
                    </svg>
                </a>
                <button type="submit" class="nav-link border-none bg-transparent">
                    <?php $name = 'logout'; include __DIR__ . '/components/icon.php'; ?>
                </button>
                
            <?php else : ?>
                <a href="/login" class="nav-link">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
