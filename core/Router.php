<?php

namespace Core;

class Router
{
    private $routes = [];
    private $params = [];
    private $middlewares = [];

    // Thêm route mới
    public function addRoute($path, $handler)
    {
        // Convert route to regex pattern
        $pattern = str_replace('/', '\/', $path);
        $pattern = '/^' . $pattern . '$/';
        $this->routes[$pattern] = $handler;
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

    // Kiểm tra và match route
    public function match($url)
    {
        foreach ($this->routes as $pattern => $handler) {
            if (preg_match($pattern, $url, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                return $handler;
            }
        }
        return false;
    }

    // Điều hướng request
    public function dispatch()
    {
        try {
            $url = $_SERVER['REQUEST_URI'];
            $url = str_replace('/agoda', '', $url);

            if (empty($url)) {
                $url = '/';
            }

            $handler = $this->match($url);

            if ($handler) {
                list($controller, $action) = explode('@', $handler);
                $controller = "App\\Controllers\\{$controller}";

                // Lấy middleware cho route hiện tại nếu có
                $middlewares = $this->middlewares[$url] ?? [];

                // Tạo callback thực thi controller
                $callback = function () use ($controller, $action) {
                    $controllerObject = new $controller();
                    return call_user_func_array([$controllerObject, $action], $this->params);
                };

                // Thực thi middleware chain
                return $this->runMiddleware($middlewares, $callback);
            }

            throw new \Exception("Route not found: {$url}");
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
        include 'app/views/errors/500.php';
    }
}
