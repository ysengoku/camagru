<div class="post-preview-card">
    <?php
        $authorName = $post['author_name'];
        $authorAvatar = $post['author_avatar'];
        $createdAt = $post['created_at'];
        include __DIR__ . '/postHeader.php';
    ?>

    <img src="<?php echo $post['image_path']; ?>" alt="Post Image" class="w-full h-auto object-cover rounded">

    <div class="flex flex-col">
        <?php
            $postId = $post['id'];
            $likesCount = $post['likes_count'];
            $commentsCount = count($post['comments']);
            include __DIR__ . '/postReactions.php';
        ?>  
    </div>
</div>
