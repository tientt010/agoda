<?php

namespace Core;

class Router
{
    private $routes = [];
    private $params = [];
    private $middlewares = [];

    public function addRoute($path, $handler)
    {
        error_log("Adding route: " . $path . " => " . $handler);
        $this->routes[$path] = [
            'pattern' => '#^' . str_replace('/', '\/', $path) . '$#',
            'handler' => $handler
        ];
    }

    /**
     * Thêm middleware cho route
     * @param string $path Đường dẫn route
     * @param string|array $middleware Tên middleware hoặc mảng middleware
     */
    public function addMiddleware($path, $middleware)
    {
        $this->middlewares[$path] = (array) $middleware;
    }

    /**
     * Thực thi middleware
     * @param array $middlewares Danh sách middleware cần thực thi
     * @param callable $next Hàm callback tiếp theo
     */
    private function runMiddleware($middlewares, $next)
    {
        if (empty($middlewares)) {
            return $next();
        }

        $middleware = array_shift($middlewares);
        $middlewareClass = "App\\Middleware\\{$middleware}";

        if (class_exists($middlewareClass)) {
            $middlewareInstance = new $middlewareClass();
            return $middlewareInstance->handle(function () use ($middlewares, $next) {
                return $this->runMiddleware($middlewares, $next);
            });
        }

        throw new \Exception("Middleware not found: {$middleware}");
    }

    public function match($url)
    {
        error_log("Matching URL: " . $url);
        // Chuẩn hóa URL trước khi match
        $url = '/' . trim($url, '/');

        foreach ($this->routes as $path => $route) {
            error_log("Testing route: {$path} against URL: {$url}");
            $pattern = '#^' . str_replace('/', '\/', $path) . '$#';
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                error_log("Route matched: " . $route['handler']);
                return $route['handler'];
            }
        }
        return false;
    }

    public function dispatch()
    {
        try {
            // Get current URL
            $fullUrl = $_SERVER['REQUEST_URI'];

            // Remove query string if exists
            $path = parse_url($fullUrl, PHP_URL_PATH);

            // Remove script name and project folder from start of URL
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath != '/') {
                $path = substr($path, strlen($basePath));
            }

            // Clean path
            $path = '/' . trim($path, '/');

            // Debug info
            error_log("Full URL: " . $fullUrl);
            error_log("Base Path: " . $basePath);
            error_log("Clean Path: " . $path);

            // Find matching route
            $handler = $this->match($path);

            if ($handler) {
                error_log("Found handler: " . $handler);
                list($controller, $action) = explode('@', $handler);
                $controller = "App\\Controllers\\{$controller}";

                $middlewares = $this->middlewares[$path] ?? [];
                $callback = function () use ($controller, $action) {
                    $controllerObject = new $controller();
                    return call_user_func_array([$controllerObject, $action], $this->params);
                };

                return $this->runMiddleware($middlewares, $callback);
            }

            throw new \Exception("No route found for: " . $path);
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Xử lý lỗi trong router
     */
    private function handleError(\Exception $e)
    {
        if (DEBUG_MODE) {
            throw $e;
        }

        error_log($e->getMessage());
        header("HTTP/1.1 500 Internal Server Error");
        include VIEW_PATH . '/errors/500.php'; // Changed from hardcoded path
    }
}
