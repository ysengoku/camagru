<?php
    $buttons = [
        ['id' => 'back-to-menu-button', 'icon' => 'back', 'text' => 'Back to Menu', 'action' => 'back-to-menu'],
        ['id' => 'capture-button', 'icon' => 'capture', 'text' => 'Capture', 'action' => 'capture'],
        ['id' => 'share-button', 'icon' => 'send', 'text' => 'Share', 'action' => 'share'],
        ['id' => 'reset-button', 'icon' => 'reset', 'text' => 'Reset', 'action' => 'reset'],
    ];
    ?>

<div class="studio-buttons flex justify-center my-4 gap-4">
    <?php foreach ($buttons as $button): ?>
        <button
            type="button"
            id="<?= $button['id'] ?>"
            class="studio-editor-button"
            data-studio-editor-action="<?= $button['action'] ?>"
        >
            <?php $name = $button['icon']; include __DIR__ . '/../components/icon.php'; ?>
            <?= $button['text'] ?>
        </button>
    <?php endforeach; ?>
</div>
