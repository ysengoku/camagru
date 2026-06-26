<?php

$routes = [
    // Feed (root page)
    ['path' => '', 'controller' => 'feed', 'action' => 'index'],

    // Post routes
    ['path' => '/post', 'controller' => 'post', 'action' => 'view'],

    // Auth routes
    ['path' => '/signup', 'method' => 'GET', 'controller' => 'auth', 'action' => 'signup'],
    ['path' => '/login', 'method' => 'GET', 'controller' => 'auth', 'action' => 'login'],
    ['path' => '/forgot-password', 'method' => 'GET', 'controller' => 'auth', 'action' => 'forgotPassword'],
    ['path' => '/reset-password', 'method' => 'GET', 'controller' => 'auth', 'action' => 'resetPassword'],
    ['path' => '/verify-email', 'method' => 'GET', 'controller' => 'auth', 'action' => 'verifyEmail'],

    // Camera/Studio routes (authenticated only)
    ['path' => '/studio', 'controller' => 'studio', 'action' => 'index', 'auth' => true],
    
    // User profile routes
    ['path' => '/profile', 'controller' => 'profile', 'action' => 'index', 'auth' => true],
    ['path' => '/profile/edit', 'controller' => 'profile', 'action' => 'edit', 'auth' => true],
    ['path' => '/profile/settings', 'controller' => 'profile', 'action' => 'settings', 'auth' => true],

    // API routes
    ['path' => '/api/signup', 'method' => 'POST', 'controller' => 'auth', 'action' => 'signup'],
    ['path' => '/api/login', 'method' => 'POST', 'controller' => 'auth', 'action' => 'login'],
    ['path' => '/api/logout', 'method' => 'POST', 'controller' => 'auth', 'action' => 'logout'],
    ['path' => '/api/forgot-password', 'method' => 'POST', 'controller' => 'auth', 'action' => 'forgotPassword'],
    ['path' => '/api/reset-password', 'method' => 'POST', 'controller' => 'auth', 'action' => 'resetPassword'],
    ['path' => '/api/verify-email', 'method' => 'POST', 'controller' => 'auth', 'action' => 'verifyEmail'],

    ['path' => '/api/studio-config', 'method' => 'GET', 'controller' => 'studioConfig', 'action' => 'config', 'auth' => true],
    ['path' => '/api/photos', 'method' => 'POST', 'controller' => 'photoApi', 'action' => 'create', 'auth' => true],
    ['path' => '/api/photos', 'method' => 'DELETE', 'controller' => 'photoApi', 'action' => 'delete', 'auth' => true],
];

return $routes;
