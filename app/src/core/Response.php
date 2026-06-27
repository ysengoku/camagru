<?php

final class Response {
    public const int OK                  = 200;
    public const int CREATED             = 201;
    public const int BAD_REQUEST         = 400;
    public const int UNAUTHORIZED        = 401;
    public const int FORBIDDEN           = 403;
    public const int NOT_FOUND           = 404;
    public const int METHOD_NOT_ALLOWED  = 405;
    public const int CONFLICT            = 409;
    public const int UNPROCESSABLE       = 422;
    public const int INTERNAL_ERROR      = 500;

    public const array STATUS_TEXTS = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
        500 => 'Internal Server Error',
    ];
    
    protected string $content = '';
    protected int $statusCode = self::OK;
    protected string $statusText = self::STATUS_TEXTS[self::OK];

    public function send(): void {
        http_response_code($this->statusCode);
        header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->statusText), true, $this->statusCode);
        error_log("Sending response with status {$this->statusCode}: {$this->statusText}");
        echo $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setStatus(int $statusCode): void {
        $this->statusCode = $statusCode;
        $this->statusText = self::STATUS_TEXTS[$statusCode] ?? 'Unknown Status';
    }

    public function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
}
