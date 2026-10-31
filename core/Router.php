<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(
        string $method,
        string $path,
        callable $handler
    ): void {
        $path = '/' . trim($path, '/');

        if ($path === '//') {
            $path = '/';
        }

        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $baseUrl = ''): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

        $basePath = '/' . trim($baseUrl, '/');

        if ($basePath !== '/' && $basePath !== '') {
            if (
                $path === $basePath ||
                str_starts_with($path, $basePath . '/')
            ) {
                $path = substr($path, strlen($basePath));
            }
        }

        $path = '/' . trim($path, '/');

        if ($path === '//') {
            $path = '/';
        }

        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo 'Página não encontrada.';
            return;
        }

        $handler();
    }
}