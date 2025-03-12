<?php

namespace Core;

class Router
{
    protected $routes = [];
    protected $params = [];
    protected $middlewares = [];

    public function addRoute($route, $handler)
    {
        $this->routes[$route] = $handler;
    }

    public function addMiddleware($route, $middleware)
    {
        $this->middlewares[$route] = $middleware;
    }

    public function match($url)
    {
        foreach ($this->routes as $route => $handler) {
            if (preg_match("#^{$route}$#", $url, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                return $handler;
            }
        }
        return false;
    }

    public function runMiddleware($middlewares, $callback)
    {
        if (empty($middlewares)) {
            return $callback();
        }

        $middleware = array_shift($middlewares);
        $middlewareClass = "App\\Middleware\\{$middleware}";
        $middlewareObject = new $middlewareClass();

        return $middlewareObject->handle($this->params, function () use ($middlewares, $callback) {
            return $this->runMiddleware($middlewares, $callback);
        });
    }

    public function dispatch()
    {
        try {
            // Get current URL and remove project folder name from it
            $url = $_SERVER['REQUEST_URI'];
            $url = str_replace(SITE_ROOT, '', $url);

            // Clean up URL
            $url = trim($url, '/');
            if (empty($url)) {
                $url = '/';
            } else {
                $url = '/' . $url;
            }

            $handler = $this->match($url);

            if ($handler) {
                list($controller, $action) = explode('@', $handler);
                $controller = "App\\Controllers\\{$controller}";

                // Get middleware for current route
                $middlewares = $this->middlewares[$url] ?? [];

                // Create controller execution callback
                $callback = function () use ($controller, $action) {
                    $controllerObject = new $controller();
                    return call_user_func_array([$controllerObject, $action], $this->params);
                };

                // Execute middleware chain
                return $this->runMiddleware($middlewares, $callback);
            }

            throw new \Exception("Route not found: {$url}");
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    protected function handleError($e)
    {
        if (DEBUG_MODE) {
            echo "<h1>Error</h1>";
            echo "<p>{$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        } else {
            // Log error
            error_log($e->getMessage());
            // Show friendly error page
            include VIEW_PATH . '/errors/500.php';
        }
    }
}
