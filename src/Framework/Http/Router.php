<?php

declare(strict_types=1);

namespace Anamorphic\Framework\Http;

use Anamorphic\Framework\Container;
use Closure;

class Router
{
    /** @var array<int, array{method: string, pattern: string, action: mixed, middleware: array}> */
    protected array $routes = [];

    protected string $groupPrefix = '';

    /** @var array<int, string> */
    protected array $groupMiddleware = [];

    public function __construct(protected Container $container)
    {
    }

    public function get(string $uri, mixed $action, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, mixed $action, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, mixed $action, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function patch(string $uri, mixed $action, array $middleware = []): void
    {
        $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    public function delete(string $uri, mixed $action, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group(string $prefix, Closure $callback, array $middleware = []): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = rtrim($previousPrefix . $prefix, '/');
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    protected function addRoute(string $method, string $uri, mixed $action, array $middleware): void
    {
        $pattern = rtrim($this->groupPrefix . '/' . ltrim($uri, '/'), '/') ?: '/';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'action' => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $params = $this->match($route['pattern'], $request->path());

            if ($params === null) {
                continue;
            }

            return $this->runMiddleware($route['middleware'], $request, function () use ($route, $params, $request) {
                return $this->callAction($route['action'], $request, $params);
            });
        }

        return Response::json(['message' => 'Not Found'], 404);
    }

    protected function match(string $pattern, string $path): ?array
    {
        $patternSegments = explode('/', trim($pattern, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($patternSegments) !== count($pathSegments)) {
            return null;
        }

        $params = [];

        foreach ($patternSegments as $index => $segment) {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                $params[trim($segment, '{}')] = $pathSegments[$index];
                continue;
            }

            if ($segment !== $pathSegments[$index]) {
                return null;
            }
        }

        return $params;
    }

    protected function runMiddleware(array $middleware, Request $request, Closure $next): Response
    {
        if (empty($middleware)) {
            return $next();
        }

        $name = array_shift($middleware);
        /** @var callable $instance */
        $instance = $this->container->make($name);

        return $instance->handle($request, function () use ($middleware, $request, $next) {
            return $this->runMiddleware($middleware, $request, $next);
        });
    }

    protected function callAction(mixed $action, Request $request, array $params): Response
    {
        if ($action instanceof Closure) {
            return $action($request, ...$params);
        }

        if (is_array($action)) {
            [$class, $method] = $action;
            $controller = $this->container->make($class);

            return $controller->{$method}($request, ...$params);
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action, 2);
            $controller = $this->container->make($class);

            return $controller->{$method}($request, ...$params);
        }

        throw new \RuntimeException('Unresolvable route action.');
    }
}
