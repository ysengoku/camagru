<?php
/**
 * @var list<PostData> $posts
 * @var int $count
 */

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
        </div>
        <?php if ($count > count($posts)) : ?>
            <div id="observer"></div>
        <?php endif; ?>
    <?php endif; ?>
</div>
