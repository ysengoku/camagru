<?php
    require_once __DIR__ . '/../../components/icon.php';
?>

<?php
if (!function_exists('render_text_tool')) {
    function render_text_tool(): string {
        $config = require __DIR__ . '/../../../config/text.php';
        $fontList = '';
        foreach ($config['fonts'] as $font) {
            $safeFont = htmlspecialchars($font, ENT_QUOTES);
            $fontList .= <<<HTML
                <option value="{$safeFont}">{$safeFont}</option>
            HTML;
        }
        $fontSizeOptions = '';
        for ($size = $config['fontSize']['min']; $size <= $config['fontSize']['max']; $size += $config['fontSize']['step']) {
            $fontSizeOptions .= <<<HTML
                <option value="{$size}">{$size}</option>
            HTML;
        }

        $addIcon = render_icon('add');
        $textColorIcon = render_icon('textcolor');

        return <<<HTML
        <div id="texttool" class="tool-container justify-around gap-4">

            <button id="text-add-btn" class="flex-col justify-center align-center border-none bg-transparent cursor-pointer">
                {$addIcon}
                Add Text
            </button>

            <div class="flex align-center">
                <!-- Font family -->
                <div class="text-style-control">
                    <label for="text-font" class="invisible">Font</label>
                    <select id="text-font">
                        {$fontList}
                    </select>
                </div>
                <!-- Font size -->
                <div class="text-style-control">
                    <label for="text-size" class="invisible">Size</label>
                    <select id="text-size">
                        {$fontSizeOptions}
                    </select>
                </div>

                <!-- Color -->
                <div class="text-style-control">
                    <label for="text-color" class="color-icon-btn">
                        {$textColorIcon}
                        <input type="color" id="text-color" value="#001919" />
                    </label>
                </div>
            </div>
        </div>
        HTML;
    }
}
?>
