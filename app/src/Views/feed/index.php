<?php
require_once __DIR__ . '/postPreview.php';
?>

<div class="p-4">
    <?php if (empty($posts)) : ?>
      <p>No posts found</p>
    <?php else : ?>
        <div id="feed-container" class="grid grid-cols-auto grid-auto-rows grid-gap-4">
        <?php foreach ($posts as $post) : ?>
            <?php echo render_post_preview($post); ?>
        <?php endforeach; ?>
        <?php if ($count > count($posts)) : ?>
            <div class="bg-frosted-glass-300 flex-col justify-center items-center rounded">
                <button id="load-more-posts-button" data-offset="<?php echo count($posts); ?>">
                    Show more posts
                </button>
            </div>
        <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
