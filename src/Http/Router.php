<?php

declare(strict_types=1);

namespace EczaneApi\Http;

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable|array $handler): self
    {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[strtoupper($method)][$normalizedPath] = $handler;
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        // CORS preflight isteği
        if ($method === 'OPTIONS') {
            return Response::json(['status' => 'ok'], 204);
        }

        // Doğrudan eşleşme kontrolü
        if (isset($this->routes[$method][$path])) {
            return $this->executeHandler($this->routes[$method][$path], $request);
        }

        // Önekli eşleşmeler (örn: /api/v1/...) veya fallback
        foreach ($this->routes[$method] ?? [] as $routePath => $handler) {
            if ($this->matchPath($routePath, $path, $params)) {
                return $this->executeHandler($handler, $request, $params);
            }
        }

        return Response::json([
            'success' => false,
            'message' => 'Bulunamadı (404 Not Found): ' . $path,
        ], 404);
    }

    private function matchPath(string $routePath, string $requestPath, &$params): bool
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#u';

        if (preg_match($pattern, $requestPath, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    private function executeHandler(callable|array $handler, Request $request, array $params = []): Response
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = is_object($class) ? $class : new $class();
            $result = $controller->$method($request, $params);
        } else {
            $result = $handler($request, $params);
        }

        if ($result instanceof Response) {
            return $result;
        }

        return Response::json($result);
    }
}
