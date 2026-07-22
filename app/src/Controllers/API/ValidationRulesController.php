<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class ValidationRulesController extends Controller {
    final public function getRules(): string {
        $rules = require __DIR__ . '/../../config/validation.php';
        
        header('Content-Type: application/json');
        echo json_encode($rules);
        exit;
    }
}
