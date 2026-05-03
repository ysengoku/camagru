<?php

abstract class Response {
    protected string $content;
    protected int $statusCode;
    protected string $statusText;

    public function send(): void {
        header("HTTP/1.1 {$this->statusCode} . ' ' . {$this->statusText}");
        echo $this->content;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setStatusCode(int $statusCode, string $statusText): void {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
    }
}
