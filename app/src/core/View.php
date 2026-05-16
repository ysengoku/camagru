<?php

class View {
    private const string LAYOUT = 'layout';
    private const string HEADER = 'header';
    private const string FOOTER = 'footer';
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
        /** @psalm-suppress UnresolvableInclude - Paths are set in constructor */
        $templatePath = $this->viewsDir . '/' . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!file_exists($templatePath)) {
            throw new HTTPNotFoundException("View template not found: " . htmlspecialchars($template));
        }

        extract($props);
        ob_start();
        include $templatePath;
        $content = (string) ob_get_clean();

        ob_start();
        /** @psalm-suppress UnresolvableInclude - Path is set in constructor */
        include $this->headerPath;
        $header = (string) ob_get_clean();

        ob_start();
        /** @psalm-suppress UnresolvableInclude - Path is set in constructor */
        include $this->footerPath;
        $footer = (string) ob_get_clean();

        ob_start();
        /** @psalm-suppress UnresolvableInclude - Path is set in constructor */
        include $this->layoutPath;
        $layout = (string) ob_get_clean();

        return $layout;
    }
}
