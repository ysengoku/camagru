<?php

final class Router {
    /**
     * @var list<array{
     * path: string,
     * controller: string,
     * action: string,
     * method?: string,
     * auth?: bool
     * }>
     */
    private array $routes;

    /**
     * Router constructor.
     * @param list<array{
     *     path: string,
     *     controller: string,
     *     action: string,
     *     method?: string,
     *     auth?: bool
     * }> $routes An array of route definitions, each containing 'path', 'controller', 'action', and optionally 'method' and 'auth'.
     */
    public function __construct(array $routes) {
        $this->routes = $routes;
    }

    /**
     * Resolves the given path and method to a route.
     * @param string $pathInfo The path to resolve.
     * @param string $method The HTTP method (GET, POST, etc.).
     * @return array{
     *     path: string,
     *     controller: string,
     *     action: string,
     *     method?: string,
     *     auth?: bool
     * }|null Returns the route parameters if found, or null if not found.
     */
    public function resolve(string $pathInfo, string $method): ?array {
        $pathMatched = false;
        foreach ($this->routes as $route) {
            if ($route['path'] === $pathInfo) {
                $pathMatched = true;
                if (!isset($route['method']) || $route['method'] === $method) {
                    return $route;
                }
            }
        }
        if ($pathMatched) {
            throw new HTTPMethodNotAllowedException();
        }

        return null;
    }
}
