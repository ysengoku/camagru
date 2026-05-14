<?php

/**
 * Filter definitions shared across the application
 * Used by: JavaScript (client), PHP (server), and CSS (visual reference)
 */
return [
    'none' => [
        'name' => 'None',
        'css' => 'none',
        'description' => 'Original image',
    ],
    'grayscale' => [
        'name' => 'Grayscale',
        'css' => 'grayscale(1)',
        'description' => 'Black and white',
    ],
    'sepia' => [
        'name' => 'Sepia',
        'css' => 'sepia(1)',
        'description' => 'Vintage brown tone',
    ],
    'vintage' => [
        'name' => 'Vintage',
        'css' => 'brightness(105%) contrast(104%) grayscale(10%) hue-rotate(0deg) invert(0%) opacity(100%) saturate(100%) sepia(50%)',
        'description' => 'Warm retro look',
    ],
    'dream' => [
        'name' => 'Dream',
        'css' => 'blur(0.6px) brightness(120%) contrast(125%) grayscale(0%) hue-rotate(342deg) invert(0%) opacity(90%) saturate(70%) sepia(15%)',
        'description' => 'Soft dreamy effect',
    ],
];
