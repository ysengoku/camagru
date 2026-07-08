<?php

final class Request {
    public static function getPathInfo(): string {
        return $_SERVER['PATH_INFO'] ?? '';
    }

    public static function isXmlHttpRequest(): bool {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function getQueryParams(): array {
        $url = $_SERVER['REQUEST_URI'] ?? '';
        $queryString = parse_url($url, PHP_URL_QUERY);
        $queryString = is_string($queryString) ? $queryString : '';
        parse_str($queryString, $queryParams);

        return $queryParams;
    }

    public static function getQueryParam(string $key): string|null {
        return self::getQueryParams()[$key] ?? null;
    }

    public static function getPostData(): array {
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

    public static function getFiles(): array {
        return $_FILES;
    }

    public static function getCsrfToken(): string {
        $token = SessionStore::get(SessionKey::CsrfToken);
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            SessionStore::set(SessionKey::CsrfToken, $token);
        }
        return $token;
    }
}
