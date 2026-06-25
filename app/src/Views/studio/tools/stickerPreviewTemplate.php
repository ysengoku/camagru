<?php
if (!function_exists('render_sticker_preview_template')) {
    /**
     * Renders the sticker preview template.
     *
     * @return string The HTML content for the sticker preview template.
     */
    function render_sticker_preview_template(): string {
        return <<<HTML
        <template id="sticker-template">
          <div class="sticker-overlay">
            <button type="button" class="sticker-delete-btn">✕</button>
            <div class="sticker-resize-handle"></div>
          </div>
        </template>
        HTML;
    }
}