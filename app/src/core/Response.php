<?php

final class Response {
    protected string $content = '';
    protected int $statusCode = 200;
    protected string $statusText = 'OK';

    public function send(): void {
        http_response_code($this->statusCode);
        header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->statusText), true, $this->statusCode);
        error_log("Sending response with status {$this->statusCode}: {$this->statusText}");
        echo $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setStatus(int $statusCode, string $statusText): void {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
    }

    public function sendApiResponse(array $data, int $statusCode, string $statusText): void {
        $this->setStatus($statusCode, $statusText);

        $contentJson = json_encode($data);
        if ($contentJson === false) {
            error_log('Failed to encode response data to JSON: ' . json_last_error_msg());
            $contentJson = '{"error":"Internal Server Error","message":"Failed to encode JSON"}';
            $this->setStatus(500, 'Internal Server Error');
        }
        $this->setContent($contentJson);
        error_log("Sending API response with status $statusCode: " . $contentJson);
        header('Content-Type: application/json', true, $statusCode);
        $this->send();
    }
}
