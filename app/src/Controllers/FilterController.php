<?php

class FilterController extends Controller {
    final public function list(): string {
        $filters = require __DIR__ . '/../config/filters.php';
        
        header('Content-Type: application/json');
        echo json_encode($filters);
        exit;
    }
}
