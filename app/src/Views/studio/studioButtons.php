<?php
    require_once __DIR__ . '/../components/icon.php';
?>

<?php
    const BUTTONS = [
        ['id' => 'back-to-menu-button', 'icon' => 'back', 'text' => 'Back to Menu', 'action' => 'back-to-menu'],
        ['id' => 'capture-button', 'icon' => 'capture', 'text' => 'Capture', 'action' => 'capture'],
        ['id' => 'share-button', 'icon' => 'send', 'text' => 'Share', 'action' => 'share'],
        ['id' => 'reset-button', 'icon' => 'reset', 'text' => 'Reset', 'action' => 'reset'],
    ];
    ?>
<?php
if (!function_exists('render_studio_buttons')) {
    function render_studio_buttons(): string {
        $buttonsHtml = '';
        foreach (BUTTONS as $button) {
            $buttonsHtml .= <<<HTML
            <button
                type="button"
                id="{$button['id']}"
                class="studio-editor-button invisible"
                data-studio-editor-action="{$button['action']}"
            >
                {$button['icon']}
                {$button['text']}
            </button>
            HTML;
        }

        return <<<HTML
        <div class="studio-buttons flex justify-center my-4 gap-4">
            {$buttonsHtml}
        </div>
        HTML;
    }
}
?>
