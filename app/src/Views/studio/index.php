<div id="studio-container">
    <div id="studio-canvas" class="studio-section">
        <?php include __DIR__ . '/studioTools.php'; ?>

        <?php include __DIR__ . '/studioMenu.php'; ?>
        <?php include __DIR__ . '/studioEditor.php'; ?>

        <?php include __DIR__ . '/studioButtons.php'; ?>
    </div>

    <?php 
        $user = $user;
        $posts = $posts;
        include __DIR__ . '/gallery.php';
    ?>
</div>
