<?php
if (!function_exists('render_text_preview')) {
    function render_text_preview(): string {
        return <<<HTML
            <div id="text-preview-container" class="position-relative">
                <span id="text-preview"></span>
                <button id="text-delete-btn" class="text-delete-btn">✕</button>
            </div>
            <div id="text-input-overlay" class="display-none flex-col align-center justify-center gap-3">
                <input type="text" id="text-input-field" placeholder="Type your text...">
                <div class="text-input-actions">
                    <button id="text-input-cancel">✕ Cancel</button>
                    <button id="text-input-confirm">✓ Confirm</button>
                </div>
            </div>
            <div id="overlay-mask" class="display-none"></div>
        HTML;
    }
}
