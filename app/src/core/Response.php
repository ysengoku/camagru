<?php

class Response {
    protected string $content = '';
    protected int $statusCode = 200;
    protected string $statusText = 'OK';

    public function send(): void {
        header(sprintf('HTTP/1.1 %d %s', $this->statusCode, $this->statusText));
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
        header('Content-Type: application/json');
        $this->setStatus($statusCode, $statusText);
        $this->setContent(json_encode($data));
        $this->send();
    }
}
