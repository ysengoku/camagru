<?php

abstract class Controller {
    protected string $actionName = 'index';
    protected int $statusCode = 200;
    protected string $statusText = 'OK';

    public function run(string $action): string {
        if (!method_exists($this, $action)) {
            throw new HTTPNotFoundException();
        }
        $this->actionName = $action;

        return $this->$action();
    }

    public function getStatus(): array {
        return ['code' => $this->statusCode, 'text' => $this->statusText];
    }

    protected function render(array $props = [], ?string $template = null): string {
        $view = new View(__DIR__ . '/../Views');
        if ($template === null) {
            $template = $this->actionName;
        }

        $controllerName = strtolower(str_replace('Controller', '', get_class($this)));
        $path = $controllerName . '/' . $template;

        $props['pageTitle'] = isset($props['pageTitle'])
            ? Application::APP_NAME . ' | ' . $props['pageTitle']
            : Application::APP_NAME . ' | ' . ucfirst($controllerName);

        // Auto-set pageScript based on controller name if not already set
        if (!isset($props['pageScript'])) {
            $props['pageScript'] = $controllerName;
        }

        return $view->render($path, $props);
    }
}
