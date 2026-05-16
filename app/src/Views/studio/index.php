<?php
/**
 * @var array $user
 * @var array $posts
 * @var array $stickers
 */
    require_once __DIR__ . '/tools/navmenu.php';
    require_once __DIR__ . '/tools/stickers.php';
    require_once __DIR__ . '/tools/text.php';
    require_once __DIR__ . '/tools/filters.php';
    require_once __DIR__ . '/studioMenu.php';
    require_once __DIR__ . '/studioButtons.php';
    require_once __DIR__ . '/gallery.php';
?>

<div id="studio-container">
    <div id="studio-canvas" class="flex-col align-center justify-center">
        <div id="studio-tools" class="disabled">
            <?php echo render_tool_menu(); ?>
            <?php echo render_stickers_tool($stickers); ?>
            <?php echo render_text_tool(); ?>
            <?php echo render_filters_tool(); ?>
        </div>

        <?php echo render_studio_menu(); ?>

        <div id="studio-editor" class="display-none m-4">
            <div id="studio-preview-text" class="display-none"></div>
            <video id="webcam" autoplay></video>
            <canvas id="studio-preview"></canvas>
            <img src="" id="studio-image" class="display-none" />
        </div>

        <?php echo render_studio_buttons(); ?>
    </div>

    <?php echo render_studio_gallery($posts); ?>
</div>
