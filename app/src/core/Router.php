<?php

final class Router {
    private array $routes;

    public function __construct(array $routes) {
        $this->routes = $routes;
    }

    public function resolve(string $pathInfo, string $method): ?array {
        foreach ($this->routes as $route) {
            if ($route['path'] === $pathInfo && (!isset($route['method']) || $route['method'] === $method)) {

                return $route;
            }
        }

        return null;
    }
}
