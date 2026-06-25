<?php

final class Request {
    public function getPathInfo(): string {
        return $_SERVER['PATH_INFO'] ?? '';
    }

    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function getQueryParams(): string {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    public function getPostData(): array {
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $input = file_get_contents('php://input');
            if ($input === false) {
                error_log('Failed to read input stream for JSON request');
                return [];
            }
            return json_decode($input, true) ?? [];
        }
        return $_POST;
    }

    public function getFiles(): array {
        return $_FILES;
    }

    public function getCsrfToken(): string {
        return $_SESSION['csrf_token'] ?? '';
    }
}
