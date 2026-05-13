<div id="studio-tools">
    <nav id="studio-tools-menu">
        <ul>
            <li class="tool-menu-item">
                <button
                    type="button"
                    id="stickers-tool-btn"
                    class="tool-active border-none bg-transparent"
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
                    class="border-none bg-transparent"
                    data-tool="textTool"
                    aria-controls="textTool"
                >
                    Text
                </button> 
            </li>

            <li class="tool-menu-item">
                <button
                    type="button"
                    id="filters-tool-btn"
                    class="border-none bg-transparent"
                    data-tool="filters"
                    aria-controls="filters"
                >
                  Filters
                </button>
            </li>
        </ul>
    </nav>
    <?php include __DIR__ . '/tools/stickers.php'; ?>
    <?php include __DIR__ . '/tools/text.php'; ?>
    <?php include __DIR__ . '/tools/filters.php'; ?>
</div>