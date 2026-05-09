
<?php $current = $_SERVER['REQUEST_URI']; ?>
<nav class="flex flex-1 justify-center align-start">
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
            <?php $size = 'medium'; $avatarPath = '/assets/img/sample-pic3.jpg'; $username = 'alice_wonder'; include __DIR__ . '/avatar.php'; ?>
        </a>
    </div>
</nav>