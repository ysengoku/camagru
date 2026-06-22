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
            $params = $this->router->resolve($this->getPathInfo(), $_SERVER['REQUEST_METHOD'] ?? 'GET');
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
        $status = $controllerInstance->getStatus();
        $this->response->setStatus($status['code'], $status['text'] ?? '');
        $this->response->setContent($content);
    }

    private function getPathInfo(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $queryPos = strpos($uri, '?');
        if ($queryPos !== false) {
            $uri = substr($uri, 0, $queryPos);
        }

        return $uri === '/' ? '' : $uri;
    }

    private function renderNotFound(): void {
        $this->response->setStatus(404, 'Not Found');
        $this->response->setContent('404 Not Found'); // TODO
    }
}
