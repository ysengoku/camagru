<?php

require_once __DIR__ . '/splAutoload.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => getenv('NODE_ENV') === 'production',
]);

$sessionHandler = new DatabaseSessionHandler();
session_set_save_handler($sessionHandler, true);
session_start();

// TODO: remove - DEBUG
if (!isset($_COOKIE['PHPSESSID'])) {
    usleep(800000);
}
