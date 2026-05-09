<div class="p-4">
    <?php if (empty($posts)) : ?>
      <p>No posts found</p>
    <?php else : ?>
        <div class="grid grid-cols-auto gap-4">
        <?php foreach ($posts as $post) : ?>
            <?php include __DIR__ . '/postPreview.php'; ?>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
