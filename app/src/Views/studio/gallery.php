<div id="studio-gallery" class="studio-section">
    <h2 class="text-center text-lg font-bold mb-4">Your Gallery</h2>
    <?php if (empty($posts)): ?>
        <p class="text-center color-primary-700">No photos yet</p>
    <?php else: ?>
        <div class="flex flex-col gap-4">
            <?php foreach ($posts as $post): ?>
                <?php 
                    $image_path = $post['image_path'];
                    $post_id = $post['id'];
                    include __DIR__ . '/galleryItem.php'; 
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>