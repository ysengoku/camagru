<div id="studio-container">
    <?php include __DIR__ . '/canvas.php'; ?>

    <?php 
        $user = $user;
        $posts = $posts;
        include __DIR__ . '/gallery.php';
    ?>
</div>

<script src="/assets/js/studio/StudioManager.js"></script>
