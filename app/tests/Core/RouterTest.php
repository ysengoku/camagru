<?php

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase {
    public function testResolveReturnsMatchingRoute(): void {
        $router = new Router([
            ['path' => '/feed', 'controller' => 'feed', 'action' => 'index', 'method' => 'GET'],
        ]);

        $route = $router->resolve('/feed', 'GET');

        $this->assertNotNull($route);
        $this->assertSame('feed', $route['controller']);
        $this->assertSame('index', $route['action']);
    }

    public function testResolveMatchesRouteWithoutMethodKey(): void {
        $router = new Router([
            ['path' => '/photos', 'controller' => 'photoApi', 'action' => 'getPhotos'],
        ]);

        $this->assertNotNull($router->resolve('/photos', 'GET'));
        $this->assertNotNull($router->resolve('/photos', 'POST'));
        $this->assertNotNull($router->resolve('/photos', 'DELETE'));
    }

    public function testResolveReturnsNullForUnknownPath(): void {
        $router = new Router([
            ['path' => '/feed', 'controller' => 'feed', 'action' => 'index', 'method' => 'GET'],
        ]);

        $this->assertNull($router->resolve('/unknown', 'GET'));
    }

    public function testResolveThrowsForMatchingPathWrongMethod(): void {
        $router = new Router([
            ['path' => '/photos', 'controller' => 'photoApi', 'action' => 'delete', 'method' => 'DELETE'],
        ]);

        $this->expectException(HTTPMethodNotAllowedException::class);
        $router->resolve('/photos', 'GET');
    }

    public function testResolveDistinguishesRoutesByMethodOnSamePath(): void {
        $router = new Router([
            ['path' => '/photos', 'controller' => 'photoApi', 'action' => 'getPhotos', 'method' => 'GET'],
            ['path' => '/photos', 'controller' => 'photoApi', 'action' => 'create', 'method' => 'POST'],
            ['path' => '/photos', 'controller' => 'photoApi', 'action' => 'delete', 'method' => 'DELETE'],
        ]);

        $this->assertSame('getPhotos', $router->resolve('/photos', 'GET')['action']);
        $this->assertSame('create', $router->resolve('/photos', 'POST')['action']);
        $this->assertSame('delete', $router->resolve('/photos', 'DELETE')['action']);
    }
}
