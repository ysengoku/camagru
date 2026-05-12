<div class="overlay-container flex align-center justify-center">
    <button class="scroll-left opacity-50 cursor-not-allowed">
        <?php $name = 'left'; include __DIR__ . '/../components/icon.php'; ?>
    </button>
    <div class="flex align-center" id="overlay-list">
        <?php foreach ($overlays as $overlay): ?>
        <button
            class="overlay-option"
            onClick="studio.selectOverlay('<?= $overlay ?>')"
        >
            <img
                src="<?= $overlay ?>"
                alt="Overlay"
                data-overlay="<?= $overlay ?>"
            />
        </button>
        <?php endforeach; ?>
    </div>
    <button class="scroll-right">
        <?php $name = 'right'; include __DIR__ . '/../components/icon.php'; ?>
    </button>
</div>