<div class="flex flex-col mb-2 px-1">
    <div class="flex align-center my-1 gap-2">
        <?php 
            $size = "small";
            $avatarPath = $authorAvatar;
            $displayName = $authorName;
            include __DIR__ . '/../components/avatar.php';
        ?>
        <p><?php echo htmlspecialchars($authorName); ?></p>
    </div>
    <p class="font-size-3 text-gray-500 my-1">
        <?php echo date('F j, Y', strtotime($createdAt)); ?>
    </p>
</div>
    