<?php
if (!function_exists('render_tool_menu')) {
    function render_tool_menu(): string {
        return <<<HTML
        <nav id="studio-tools-menu">
            <ul>
                <li class="tool-menu-item">
                    <button
                        type="button"
                        id="stickers-tool-btn"
                        class="tool-active border-none bg-transparent cursor-pointer"
                        data-tool="stickers"
                        aria-controls="stickers"
                    >
                        Stickers
                    </button>
                </li>

                <li class="tool-menu-item">
                    <button
                        type="button"
                        id="text-tool-btn"
                        class="border-none bg-transparent cursor-pointer"
                        data-tool="texttool"
                        aria-controls="texttool"
                    >
                        Text
                    </button> 
                </li>

                <li class="tool-menu-item">
                    <button
                        type="button"
                        id="filters-tool-btn"
                        class="border-none bg-transparent cursor-pointer"
                        data-tool="filters"
                        aria-controls="filters"
                    >
                      Filters
                    </button>
                </li>
            </ul>
        </nav>
        HTML;
    }
}
?>
