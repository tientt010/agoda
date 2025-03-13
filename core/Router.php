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
        error_log("Running middleware: {$middlewareClass}");

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

        // Exact match first (most common case)
        if (isset($this->routes[$url])) {
            error_log("Exact route match found: " . $this->routes[$url]['handler']);
            return $this->routes[$url]['handler'];
        }

        // Pattern matching for routes with parameters
        foreach ($this->routes as $path => $route) {
            error_log("Testing route pattern: {$route['pattern']} against URL: {$url}");

            if (preg_match($route['pattern'], $url, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                error_log("Route matched: " . $route['handler'] . " with params: " . print_r($matches, true));
                return $route['handler'];
            }
        }

        error_log("No matching route found for: " . $url);
        return false;
    }

    public function dispatch()
    {
        try {
            // Start the session if it hasn't been started yet
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Get current URL
            $fullUrl = $_SERVER['REQUEST_URI'];

            // Remove query string if exists
            $path = parse_url($fullUrl, PHP_URL_PATH);

            // Remove script name and project folder from start of URL
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath != '/' && strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
            }

            // Clean path
            $path = '/' . trim($path, '/');

            // Debug info
            error_log("Dispatch request - Full URL: " . $fullUrl);
            error_log("Dispatch request - Base Path: " . $basePath);
            error_log("Dispatch request - Clean Path: " . $path);
            error_log("SITE_URL: " . SITE_URL);
            error_log("Session data: " . (isset($_SESSION) ? print_r($_SESSION, true) : "No session data"));

            // Find matching route
            $handler = $this->match($path);

            if ($handler) {
                error_log("Found handler: " . $handler);
                list($controller, $action) = explode('@', $handler);

                // Use proper controller namespace
                $controller = NAMESPACE_CONTROLLERS . '\\' . $controller;
                error_log("Looking for controller class: " . $controller);

                // Check if controller file exists physically
                $controllerFile = ROOT_PATH . '/app/controllers/' . basename(str_replace('\\', '/', $controller)) . '.php';
                error_log("Checking controller file: " . $controllerFile);
                if (!file_exists($controllerFile)) {
                    error_log("Controller file not found: " . $controllerFile);

                    // Try to find the file with case-insensitive check
                    $dir = dirname($controllerFile);
                    $filename = basename($controllerFile);
                    $found = false;

                    if (is_dir($dir)) {
                        foreach (scandir($dir) as $file) {
                            if (strtolower($file) === strtolower($filename)) {
                                require_once $dir . '/' . $file;
                                $found = true;
                                error_log("Found controller with different case: " . $dir . '/' . $file);
                                break;
                            }
                        }
                    }

                    if (!$found) {
                        throw new \Exception("Controller file not found: {$controllerFile}");
                    }
                }

                // Check if controller class exists
                if (!class_exists($controller)) {
                    throw new \Exception("Controller not found: {$controller}");
                }

                error_log("Controller class exists. Checking for middleware...");
                $middlewares = $this->middlewares[$path] ?? [];
                if (!empty($middlewares)) {
                    error_log("Middlewares for this path: " . print_r($middlewares, true));
                }

                $callback = function () use ($controller, $action) {
                    error_log("Creating controller instance: {$controller}");
                    $controllerObject = new $controller();
                    error_log("Executing action: {$action}");
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
        error_log("Router error: " . $e->getMessage());
        error_log($e->getTraceAsString());

        // Thêm thông tin debug chi tiết
        error_log("Available classes in app/controllers:");
        $controllerDir = ROOT_PATH . '/app/controllers';
        if (is_dir($controllerDir)) {
            $files = scandir($controllerDir);
            error_log(print_r($files, true));
        } else {
            error_log("Controller directory not found: " . $controllerDir);
        }

        if (DEBUG_MODE) {
            throw $e;
        }

        header("HTTP/1.1 500 Internal Server Error");
        include VIEW_PATH . '/errors/500.php';
    }
}
