<?php
    const SAMPLE_IMAGE  = '/assets/img/sample-pic2.jpg';
    const FILTERS = ['none', 'grayscale', 'sepia', 'vintage', 'dream'];
    const DEFAULT_FILTER = 'none';
?>

<?php
if (!function_exists('render_filters_tool')) {
    function render_filters_tool(): string {
        $sampleImageHtml = htmlspecialchars(SAMPLE_IMAGE, ENT_QUOTES);
        $stickersHtml = '';
        foreach (FILTERS as $filter) {
            $filterHtml = htmlspecialchars($filter, ENT_QUOTES);
            $stickersHtml .= <<<HTML
            <div class="flex-col align-center">
                <button
                    type="button"
                    id="filter-{$filterHtml}"
                    class="filter-option " . ($filter === DEFAULT_FILTER ? 'selected-filter' : '')
                    data-filter="{$filterHtml}"
                >
                    <img
                        src="{$sampleImageHtml}"
                        alt="{$filterHtml}"
                        class="filter-{$filterHtml}"
                    />
                </button>
                <span class="font-size-3">{$filterHtml}</span>
            </div>
            HTML;
        }

        return <<<HTML
        <div id="filters" class="tool-container justify-center gap-4 display-none">
            {$stickersHtml}
        </div>
        HTML;
    }
}
?>
