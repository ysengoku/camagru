<?php

require_once __DIR__ . '/helper/Path.php';
require_once __DIR__ . '/helper/renderer.php';
require_once __DIR__ . '/helper/token.php';

final class Application {
    private   Router   $router;
    protected Response $response;

    public  const string APP_NAME            = 'Camagru';
    private const array  ALLOWED_METHODS     = ['GET', 'POST', 'DELETE'];
    private const array  CSRF_EXEMPT_METHODS = ['GET'];

    public function __construct() {
        $this->router   = new Router(require __DIR__ . '/config/routes.php');
        $this->response = new Response();
    }

    /**
     * Runs the application by resolving the current request and executing the corresponding controller action.
     */
    public function run(): void {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if (!in_array($method, self::ALLOWED_METHODS)) {
                error_log("Method not allowed: " . $method);
                throw new HTTPNotFoundException();
            }

            $params = $this->router->resolve($this->getPathInfo(), $_SERVER['REQUEST_METHOD'] ?? 'GET');
            if ($params === null) {
                error_log("Route not found for URI: " . $this->getPathInfo());
                throw new HTTPNotFoundException();
            }

            $csrfToken = Request::getCsrfToken();
            $receivedCsrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!in_array($method, self::CSRF_EXEMPT_METHODS) && !hash_equals($csrfToken, $receivedCsrfToken)) {
                $this->response->setStatus(Response::FORBIDDEN);
                $this->response->setContent('403 Forbidden: Invalid CSRF token');
                $this->response->send();
                return;
            }

            $user = User::getCurrentUser();
            $authRequired = $params['auth'] ?? false;
            if ($authRequired === true && $user === null) {
                if (str_starts_with($params['path'], '/api/')) {
                    $this->response->setStatus(Response::UNAUTHORIZED);
                    $this->response->setContent(json_encode(['error' => 'Authentication required']));
                    $this->response->send();
                    return;
                }
                $this->response->redirect('/login');
            }

            $controller = $params['controller'];
            $action     = $params['action'];
            $this->runAction($controller, $action, $user);
        } catch (HTTPMethodNotAllowedException $e) {
            error_log('HTTPMethodNotAllowedException: ' . $e->getMessage());
            $this->response->setStatus(Response::METHOD_NOT_ALLOWED);
            $this->response->setContent('405 Method Not Allowed');
        } catch (HTTPNotFoundException $e) {
            error_log('HTTPNotFoundException: ' . $e->getMessage());
            $this->renderNotFound();
        }

        $this->response->send();
    }

    private function runAction(string $controller, string $action, ?User $user): void {
        $controllerClass = ucfirst($controller) . 'Controller';
        if (!class_exists($controllerClass)) {
            error_log("Controller class not found: " . htmlspecialchars($controllerClass));
            throw new HTTPNotFoundException();
        }

        /** @psalm-suppress MixedMethodCall */
        $controllerInstance = new $controllerClass($user);

        /** @var Controller $controllerInstance */
        $content = $controllerInstance->run($action);

        /** @var array{code: int, text?: string} $status */
        $status = $controllerInstance->getStatus();
        
        $this->response->setStatus($status['code']);
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
        $user = User::getCurrentUser();
        $controller = new ErrorController($user);
        $content = $controller->run('notFound');
        $this->response->setStatus(Response::NOT_FOUND);
        $this->response->setContent($content);
    }
}
