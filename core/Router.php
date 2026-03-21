<?php

namespace Core;

class Router
{
    private static array $routes = [];
    private static array $middlewares = [];

    public static function get(string $path, string $action, array $middleware = []): void
    {
        self::addRoute('GET', $path, $action, $middleware);
    }

    public static function post(string $path, string $action, array $middleware = []): void
    {
        self::addRoute('POST', $path, $action, $middleware);
    }

    public static function put(string $path, string $action, array $middleware = []): void
    {
        self::addRoute('PUT', $path, $action, $middleware);
    }

    public static function delete(string $path, string $action, array $middleware = []): void
    {
        self::addRoute('DELETE', $path, $action, $middleware);
    }

    public static function group(array $options, callable $callback): void
    {
        $previousMiddlewares = self::$middlewares;

        if (isset($options['middleware'])) {
            $mw = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            self::$middlewares = array_merge(self::$middlewares, $mw);
        }

        $prefix = $options['prefix'] ?? '';

        $callback($prefix);

        self::$middlewares = $previousMiddlewares;
    }

    private static function addRoute(string $method, string $path, string $action, array $middleware = []): void
    {
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
            'middleware' => array_merge(self::$middlewares, $middleware),
        ];
    }

    public function dispatch(): void
    {
        $uri = trim($_GET['url'] ?? '', '/');
        $method = $_SERVER['REQUEST_METHOD'];

        // Support PUT/DELETE via _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            $pattern = $this->convertToRegex($route['path']);

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $middlewareClass = "App\\Middleware\\{$mw}";
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }
                }

                // Parse controller@method
                [$controllerName, $methodName] = explode('@', $route['action']);
                $controllerClass = "App\\Controllers\\{$controllerName}";

                if (!class_exists($controllerClass)) {
                    $this->error404();
                    return;
                }

                $controller = new $controllerClass();

                // Extract route parameters (named groups from regex)
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                call_user_func_array([$controller, $methodName], $params);
                return;
            }
        }

        $this->error404();
    }

    private function convertToRegex(string $path): string
    {
        $path = trim($path, '/');
        $path = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $path . '$#';
    }

    private function error404(): void
    {
        http_response_code(404);
        if (file_exists(BASE_PATH . '/resources/views/errors/404.php')) {
            require BASE_PATH . '/resources/views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }
}
