<?php

class Application {
    private Router $router;
    protected Response $response;

    public function __construct() {
        $this->router   = new Router(require __DIR__ . '/config/routes.php');
        $this->response = new Response();
    }

    public function run(): void {
        try {
            $params = $this->router->resolve($this->getPathInfo());
            if ($params === null) {
                throw new HTTPNotFoundException();
            }

            $controller = $params['controller'];
            $action     = $params['action'];
            $this->runAction($controller, $action);
        } catch (HTTPNotFoundException $e) {
            $this->renderNotFound();
        }

        $this->response->send();
    }

    private function runAction(string $controller, string $action): void {
        $controllerClass = ucfirst($controller) . 'Controller';
        if (!class_exists($controllerClass)) {
            throw new HTTPNotFoundException();
        }

        $controllerInstance = new $controllerClass();
        $content = $controllerInstance->run($action);
        $this->response->setContent($content);
    }

    private function getPathInfo(): string {
        return $_SERVER['PATH_INFO'] ?? '/';
    }

    private function renderNotFound(): void {
        $this->response->setStatusCode(404, 'Not Found');
        $this->response->setContent('404 Not Found'); // TODO
    }
}
