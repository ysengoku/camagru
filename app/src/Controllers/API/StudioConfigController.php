<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class StudioConfigController extends Controller {
    final public function config(): string {
        if (Request::getMethod() !== 'GET') {
            return $this->methodNotAllowed();
        }

        $config = require __DIR__ . '/../../config/studio.php';
        
        header('Content-Type: application/json');
        echo json_encode($config);
        exit;
    }
}
