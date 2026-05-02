<?php

$routes = [
    // Feed (root page)
    ['path' => '/', 'controller' => 'feed', 'action' => 'index'],

    // Auth routes
    ['path' => '/login', 'controller' => 'auth', 'action' => 'login'],
    ['path' => '/signup', 'controller' => 'auth', 'action' => 'signup'],
    ['path' => '/forgot-password', 'controller' => 'auth', 'action' => 'forgotPassword'],
    ['path' => '/reset-password', 'controller' => 'auth', 'action' => 'resetPassword'],
    ['path' => '/logout', 'controller' => 'auth', 'action' => 'logout'],

    // Camera/Studio routes (authenticated only)
    ['path' => '/studio', 'controller' => 'studio', 'action' => 'index'],
    ['path' => '/studio/capture', 'controller' => 'studio', 'action' => 'capture'],
    ['path' => '/studio/upload', 'controller' => 'studio', 'action' => 'upload'],
    ['path' => '/studio/delete', 'controller' => 'studio', 'action' => 'delete'],

    // User profile routes
    ['path' => '/profile', 'controller' => 'profile', 'action' => 'index'],
    ['path' => '/profile/edit', 'controller' => 'profile', 'action' => 'edit'],
    ['path' => '/profile/settings', 'controller' => 'profile', 'action' => 'settings'],
];

return $routes;
