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
    ['path' => '/email-sent', 'method' => 'GET', 'controller' => 'auth', 'action' => 'emailSent'],

    // Camera/Studio routes (authenticated only)
    ['path' => '/studio', 'controller' => 'studio', 'action' => 'index', 'auth' => true],
    
    // User profile routes
    ['path' => '/profile', 'controller' => 'profile', 'action' => 'index', 'auth' => true],

    // API routes
    ['path' => '/api/validation-rules', 'method' => 'GET', 'controller' => 'validationRules', 'action' => 'getRules'],
    ['path' => '/api/signup', 'method' => 'POST', 'controller' => 'auth', 'action' => 'signup'],
    ['path' => '/api/login', 'method' => 'POST', 'controller' => 'auth', 'action' => 'login'],
    ['path' => '/api/logout', 'method' => 'POST', 'controller' => 'auth', 'action' => 'logout', 'auth' => true],
    ['path' => '/api/forgot-password', 'method' => 'POST', 'controller' => 'auth', 'action' => 'forgotPassword'],
    ['path' => '/api/reset-password', 'method' => 'POST', 'controller' => 'auth', 'action' => 'resetPassword'],
    ['path' => '/api/verify-email', 'method' => 'POST', 'controller' => 'auth', 'action' => 'verifyEmail'],
    ['path' => '/api/resend-email', 'method' => 'POST', 'controller' => 'auth', 'action' => 'resendEmail'],
    ['path' => '/api/profile', 'method' => 'POST', 'controller' => 'profile', 'action' => 'update', 'auth' => true],

    ['path' => '/api/studio-config', 'method' => 'GET', 'controller' => 'studioConfig', 'action' => 'config', 'auth' => true],
    ['path' => '/api/photos', 'method' => 'POST', 'controller' => 'photoApi', 'action' => 'create', 'auth' => true],
    ['path' => '/api/photos', 'method' => 'DELETE', 'controller' => 'photoApi', 'action' => 'delete', 'auth' => true],
];

return $routes;
