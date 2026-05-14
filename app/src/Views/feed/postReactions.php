<div class="flex items-center mt-4 gap-4">
    <div class="post-reactions">
        <button
          type="button"
          class="flex align-center gap-1 border-none bg-transparent p-0 cursor-pointer hover-scale"
          data-like="<?php echo $postId; ?>"
        >
            <?php $name = 'heart'; include __DIR__ . '/../components/icon.php'; ?>
            <?php if ($likesCount > 0) : ?>
            <p class="text-gray-500">
                <?php echo $likesCount; ?>
            </p>
            <?php endif; ?>
        </button>
    </div>

    <div class="post-reactions">
        <button 
          type="button"
          class="flex align-center gap-1 border-none bg-transparent p-0 cursor-pointer hover-scale"
          data-comment="<?php echo $postId; ?>"
        >
            <?php $name = 'bubble'; include __DIR__ . '/../components/icon.php'; ?>
            <?php if ($commentsCount > 0) : ?>
            <p class="text-gray-500">
                <?php echo $commentsCount; ?>
            </p>
            <?php endif; ?>
        </button>
    </div>
</div> 
