<?php

abstract class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve($pathInfo)
    {
        foreach ($this->routes as $route) {
            if ($route['path'] === $pathInfo) {
                return $route;
            }
        }
        return null;
    }
}
