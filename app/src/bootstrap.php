<?php

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/' . $class . '.php',
        __DIR__ . '/Core/' . $class . '.php',
        __DIR__ . '/Controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
