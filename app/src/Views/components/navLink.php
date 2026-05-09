
<?php $current = $_SERVER['REQUEST_URI']; ?>
<nav class="flex flex-1 justify-center">
    <div class="nav-link-container <?php echo $current === '/' ?'nav-link-active' : ''; ?>">
        <a href="/" class="px-1 nav-link">
            <?php $name = 'gallery'; include __DIR__ . '/icon.php'; ?>
        </a>
    </div>
    <div class="nav-link-container <?php echo strpos($current, 'edit') === 0 ? 'nav-link-active' : ''; ?>">
        <a href="/edit" class="px-1 nav-link">
            <?php $name = 'camera'; include __DIR__ . '/icon.php'; ?>
        </a>
    </div>
    <div class="nav-link-container <?php echo strpos($current, 'profile') === 0 ? 'nav-link-active' : ''; ?>">
        <a href="/profile" class="px-1 nav-link">
            <?php $name = 'profile'; include __DIR__ . '/icon.php'; ?>
        </a>
    </div>
</nav>