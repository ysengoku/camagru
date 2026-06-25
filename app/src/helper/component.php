<?php

/**
 * Helper function to render a component template with the given properties.
 *
 * @param string $name The name of the component template (relative to the components directory).
 * @param array<string, mixed> $props An associative array of properties to pass to the component.
 * @return string The rendered HTML content of the component.
 */
function component(string $name, array $props = []): string {
    $view = new View(__DIR__ . '/../Views');
    return $view->render('components/' . $name, $props);
}
