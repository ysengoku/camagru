<?php
/**
 * @var array $post
 */
?>

<?php foreach ($post['comments'] as $comment) : ?>
    <div class="flex flex-col gap-2 px-1 mb-4 color-gray-700">
        <p class="m-0"><?php echo htmlspecialchars($comment['author_name']); ?></p>
        <p class="m-0"><?php echo htmlspecialchars($comment['text']); ?> </p>
    </div>
<?php endforeach; ?>