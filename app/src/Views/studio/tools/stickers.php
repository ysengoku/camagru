<?php
    require_once __DIR__ . '/../../components/icon.php';

if (!function_exists('render_stickers_tool')) {
    function render_stickers_tool(array $stickers): string {
        $leftIcon = render_icon('left');
        $rightIcon = render_icon('right');
        $stickerButtons = '';
        foreach ($stickers as $sticker) {
            $safeSticker = htmlspecialchars($sticker, ENT_QUOTES);
            $stickerButtons .= <<<HTML
            <button
                class="stickers-option"
                data-sticker="{$safeSticker}"
            >
                <img
                    src="{$safeSticker}"
                    alt="Sticker"
                    data-sticker="{$safeSticker}"
                />
            </button>
            HTML;
        }

        return <<<HTML
        <div id="stickers" class="tool-container justify-center">
            <button class="scroll-left opacity-50 cursor-not-allowed">
                {$leftIcon}
            </button>
            <div class="flex align-center" id="sticker-list">
                {$stickerButtons}
            </div>
            <button class="scroll-right">
                {$rightIcon}
            </button>
        </div>
        HTML;
    }
}
