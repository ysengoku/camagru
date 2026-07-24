<?php

/**
 * A lightweight rendering engine that handles view templates, 
 * extracts dynamic properties into local variables,
 * and aggregates them into a global layout using output buffering.
 */
final class View {
    private const string TITLE = 'Camagru';
    private const string LAYOUT = 'layout';
    private const string HEADER = 'header';
    private const string FOOTER = 'footer';
    private string $viewsDir;
    private string $layoutPath;
    private string $headerPath;
    private string $footerPath;

    /* *
     * View constructor.
     * * @param string $viewsDir The absolute root directory where all template files reside.
     */
    public function __construct(string $viewsDir) {
        $this->viewsDir = rtrim($viewsDir, '/');
        $this->layoutPath = $this->viewsDir . '/' . self::LAYOUT . '.php';
        $this->headerPath = $this->viewsDir . '/' . self::HEADER . '.php';
        $this->footerPath = $this->viewsDir . '/' . self::FOOTER . '.php';
    }

    /**
     * Renders a specific template file populated with the provided data context
     * and wraps it inside the global layout skeleton.
     * * @psalm-suppress UnusedParam - Suppressed because $template and $props are utilized 
     * indirectly within the isolated scope of the included templates.
     * @param string $template The relative path to the template file (e.g., 'posts/index').
     * @param array<string, mixed> $props An associative array of data to expand into variables within the templates.
     * @return string The fully assembled and compiled HTML raw content.
     * @throws HTTPNotFoundException If the requested template file cannot be found on the filesystem.
     * 
     * @psalm-suppress UnusedParam - Variables are used inside the included template files
     */
    public function render(string $template, array $props = []): string {
        extract($props);
        ob_start();
        $title = self::TITLE;

        $content = $this->renderContent($template, $props);

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

    /**
     * @psalm-suppress UnusedParam - $template and $props are used, but extract($props) makes
     * Psalm treat every variable in this method as possibly redefined
     */
    public function renderContent(string $template, array $props = []): string {
        /** @psalm-suppress UnresolvableInclude - Paths are set in constructor */
        $templatePath = $this->viewsDir . '/' . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!file_exists($templatePath)) {
            error_log("View template not found: " . htmlspecialchars($template));
            throw new HTTPNotFoundException();
        }

        extract($props);
        ob_start();
        include $templatePath;
        return (string) ob_get_clean();
    }
}
