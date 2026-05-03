<?php

class View {
    private string $viewsPath;

    public function __construct(string $viewsPath) {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    public function render(string $template, array $props = []): string {
        $templatePath = $this->viewsPath . '/' . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!file_exists($templatePath)) {
            throw new HTTPNotFoundException("View template not found: " . htmlspecialchars($template));
        }

        extract($props);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}
