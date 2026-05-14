<?php

function component(string $name, array $props = []): string {
    $view = new View(__DIR__ . '/../Views');
    return $view->render('components/' . $name, $props);
}
