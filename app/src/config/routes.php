<?php

$routes = [
    // Feed (root page)
    ['path' => '', 'controller' => 'feed', 'action' => 'index'],

    // Post routes
    ['path' => '/post/', 'controller' => 'post', 'action' => 'view'],

    // Auth routes
    ['path' => '/login', 'controller' => 'auth', 'action' => 'login'],
    ['path' => '/signup', 'controller' => 'auth', 'action' => 'signup'],
    ['path' => '/forgot-password', 'controller' => 'auth', 'action' => 'forgotPassword'],
    ['path' => '/reset-password', 'controller' => 'auth', 'action' => 'resetPassword'],
    ['path' => '/logout', 'controller' => 'auth', 'action' => 'logout'],

    // Camera/Studio routes (authenticated only)
    ['path' => '/studio', 'controller' => 'studio', 'action' => 'index'],

    // API routes
    // ['path' => '/api/text-config', 'controller' => 'text', 'action' => 'config'],
    // ['path' => '/api/filters', 'controller' => 'filter', 'action' => 'list'],
    ['path' => '/api/studio-config', 'controller' => 'studioConfig', 'action' => 'config'],

    // User profile routes
    ['path' => '/profile', 'controller' => 'profile', 'action' => 'index'],
    ['path' => '/profile/edit', 'controller' => 'profile', 'action' => 'edit'],
    ['path' => '/profile/settings', 'controller' => 'profile', 'action' => 'settings'],
];

return $routes;
