<?php

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/' . $class . '.php',
        __DIR__ . '/core/' . $class . '.php',
        __DIR__ . '/Controllers/' . $class . '.php',
        __DIR__ . '/Models/' . $class . '.php',
        __DIR__ . '/DTO/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }

    });
    
$sessionHandler = new SessionHandler();
session_set_save_handler($sessionHandler, true);
session_start();
