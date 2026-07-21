<?php
/**
 * @var array $user
 * @var array $posts
 * @var int   $postCount
 * @var array $stickers
 */
    require_once __DIR__ . '/tools/navmenu.php';
    require_once __DIR__ . '/tools/stickers.php';
    require_once __DIR__ . '/tools/stickerPreviewTemplate.php';
    require_once __DIR__ . '/tools/text.php';
    require_once __DIR__ . '/tools/textPreview.php';
    require_once __DIR__ . '/tools/filters.php';
    require_once __DIR__ . '/studioMenu.php';
    require_once __DIR__ . '/studioButtons.php';
    require_once __DIR__ . '/gallery.php';
?>

<div id="studio">
<div id="studio-container">
    <div id="studio-canvas" class="flex-col bg-frosted-glass-200">
        <div id="studio-tools" class="disabled">
            <?php echo render_tool_menu(); ?>
            <?php echo render_stickers_tool($stickers); ?>
            <?php echo render_text_tool(); ?>
            <?php echo render_filters_tool(); ?>
        </div>

        <?php echo render_studio_menu(); ?>

        <div id="studio-editor" class="display-none m-4">
            <video id="webcam" autoplay></video>
            <canvas id="studio-preview"></canvas>
            <img id="studio-image" class="display-none" />

            <?php echo render_text_preview(); ?>
            <?php echo render_sticker_preview_template(); ?>
        </div>

        <?php echo render_studio_buttons(); ?>
    </div>

    <?php echo render_studio_gallery($posts, $postCount); ?>
</div>
</div>
