<?php

abstract class Controller {
    protected string $actionName;

    public function run($action) {
        if (!method_exists($this, $action)) {
            throw new HTTPNotFoundException();
        }
        $this->actionName = $action;
        return $this->$action();
    }

    protected function render($props = [], $template = null): string {
        $view = new View(__DIR__ . '/../Views');
        if (!$template) {
            $template = $this->actionName;
        }

        $controllerName = strtolower(str_replace('Controller', '', get_class($this)));
        $path = $controllerName . '/' . $template;

        return $view->render($path, $props);
    }
}
