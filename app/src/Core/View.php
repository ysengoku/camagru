<?php

class View {
    private const LAYOUT = 'layout';
    private const HEADER = 'header';
    private const FOOTER = 'footer';
    private string $viewsDir;
    private string $layoutPath;
    private string $headerPath;
    private string $footerPath;

    public function __construct(string $viewsDir) {
        $this->viewsDir = rtrim($viewsDir, '/');
        $this->layoutPath = $this->viewsDir . '/layout.php';
        $this->headerPath = $this->viewsDir . '/header.php';
        $this->footerPath = $this->viewsDir . '/footer.php';
    }

    public function render(string $template, array $props = []): string {
        $templatePath = $this->viewsDir . '/' . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!file_exists($templatePath)) {
            throw new HTTPNotFoundException("View template not found: " . htmlspecialchars($template));
        }

        extract($props);
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        ob_start();
        include $this->headerPath;
        $header = ob_get_clean();

        ob_start();
        include $this->footerPath;
        $footer = ob_get_clean();

        ob_start();
        include $this->layoutPath;
        $layout = ob_get_clean();

        return $layout;
    }
}
