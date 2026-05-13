<div id="stickers" class="tool-container justify-center">
    <button class="scroll-left opacity-50 cursor-not-allowed">
        <?php $name = 'left'; include __DIR__ . '/../../components/icon.php'; ?>
    </button>
    <div class="flex align-center" id="sticker-list">
        <?php foreach ($stickers as $sticker): ?>
        <button
            class="stickers-option"
            onClick="studio.selectSticker('<?= $sticker ?>')"
        >
            <img
                src="<?= $sticker ?>"
                alt="Sticker"
                data-sticker="<?= $sticker ?>"
            />
        </button>
        <?php endforeach; ?>
    </div>
    <button class="scroll-right">
        <?php $name = 'right'; include __DIR__ . '/../../components/icon.php'; ?>
    </button>
</div>