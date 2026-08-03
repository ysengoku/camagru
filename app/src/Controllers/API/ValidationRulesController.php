<?php

/**
 * @psalm-suppress UnusedClass - Instantiated dynamically via routing
 */
final class ValidationRulesController extends Controller {
    /**
     * Returns the client-side validation rules (username, password, email)
     * used to validate signup/profile forms.
     *
     * @route GET /api/validation-rules
     * @response 200 {username, password, email} Validation constraints and error messages per field
     */
    final public function getRules(): string {
        $rules = require __DIR__ . '/../../config/validation.php';
        
        return $this->json($rules, Response::OK);
    }
}
