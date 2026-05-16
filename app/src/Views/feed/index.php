<?php
    require_once __DIR__ . '/postPreview.php';
?>

<div class="p-4">
    <?php if (empty($posts)) : ?>
      <p>No posts found</p>
    <?php else : ?>
        <div class="grid grid-cols-auto gap-4">
        <?php foreach ($posts as $post) : ?>
            <?php echo render_post_preview($post); ?>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
