<?php

abstract class Controller {
    protected string $actionName;

    public function run(string $action): string {
        if (!method_exists($this, $action)) {
            throw new HTTPNotFoundException();
        }
        $this->actionName = $action;
        return $this->$action();
    }

    protected function render(array $props = [], ?string $template = null): string {
        $view = new View(__DIR__ . '/../Views');
        if ($template === null) {
            $template = $this->actionName;
        }

        $controllerName = strtolower(str_replace('Controller', '', get_class($this)));
        $path = $controllerName . '/' . $template;

        return $view->render($path, $props);
    }
}
