<?php
/**
 * Render an SVG icon by name.
 */
if (!function_exists('render_icon')) {
    function render_icon(string $name): string {
        $safe = htmlspecialchars($name, ENT_QUOTES);

        return <<< HTML
        <svg viewBox="0 0 640 640" fill="currentColor">
            <use xlink:href="/assets/icons/{$safe}.svg#{$safe}-icon"></use>
        </svg>
        HTML;
    }
}
