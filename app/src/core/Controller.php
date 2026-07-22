<?php

abstract class Controller {
    protected string $actionName  = 'index';
    protected int    $statusCode  = 200;
    protected string $statusText  = 'OK';
    protected ?User  $currentUser = null;

    public function __construct(?User $currentUser = null) {
        $this->currentUser = $currentUser;
    }

    public function run(string $action): string {
        if (!method_exists($this, $action)) {
            error_log("Action method not found: " . htmlspecialchars($action) . " in controller " . get_class($this));
            throw new HTTPNotFoundException();
        }
        $this->actionName = $action;

        return $this->$action();
    }

    public function getStatus(): array {
        return ['code' => $this->statusCode, 'text' => $this->statusText];
    }

    protected function json(array $data, int $statusCode): string {
        $this->statusCode = $statusCode;
        $this->statusText = Response::STATUS_TEXTS[$statusCode] ?? 'Unknown Status';
        header('Content-Type: application/json');
        $json = json_encode($data);

        return $json === false ? '{"error":"Internal Server Error"}' : $json;
    }

    protected function render(array $props = [], ?string $template = null): string {
        [$path, $controllerName, $props] = $this->prepareRender($props, $template);

        $props['pageTitle'] = isset($props['pageTitle'])
            ? Application::APP_NAME . ' | ' . $props['pageTitle']
            : Application::APP_NAME . ' | ' . ucfirst($controllerName);

        return (new View(__DIR__ . '/../Views'))->render($path, $props);
    }

    protected function renderContent(array $props = [], ?string $template = null): string {
        [$path, , $props] = $this->prepareRender($props, $template);

        return (new View(__DIR__ . '/../Views'))->renderContent($path, $props);
    }

    /**
     * Shared setup for render()/renderContent(): resolves the controller-namespaced
     * template path and fills in the props common to both a full page and a partial.
     * @return array{0: string, 1: string, 2: array<string, mixed>}
     */
    private function prepareRender(array $props, ?string $template): array {
        $template ??= $this->actionName;
        $controllerName = strtolower(str_replace('Controller', '', get_class($this)));
        $path = $controllerName . '/' . $template;

        $props['user'] = $this->currentUser;

        // Auto-set pageScript based on controller name if not already set
        if (!isset($props['pageScript'])) {
            $props['pageScript'] = $controllerName;
        }

        return [$path, $controllerName, $props];
    }

    protected function methodNotAllowed(): string {
        return $this->json(['error' => 'Method Not Allowed'], Response::METHOD_NOT_ALLOWED);
    }
}
