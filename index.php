<?php

// Error reporting first
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load paths config
require_once __DIR__ . '/config/paths.php';
require_once __DIR__ . '/config/namespaces.php';

// Debug log router namespace definitions
error_log("NAMESPACE_APP: " . NAMESPACE_APP);
error_log("NAMESPACE_CONTROLLERS: " . NAMESPACE_CONTROLLERS);

// Load main config using ROOT_PATH instead of PROJECT_PATH
require_once ROOT_PATH . '/config/config.php';

// Debug logging setup
$logPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($logPath)) {
    mkdir($logPath, 0755, true);
}
$logFile = $logPath . DIRECTORY_SEPARATOR . 'debug.log';
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0666);
}
ini_set('log_errors', 1);
ini_set('error_log', $logFile);

// Load other configs
require_once CONFIG_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/BaseController.php';
require_once CORE_PATH . '/BaseModel.php';
require_once CORE_PATH . '/ErrorHandler.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Khởi tạo ErrorHandler
Core\ErrorHandler::init();

// Thêm đoạn code này trước khi gọi autoloader để đảm bảo tệp Middleware.php được load
require_once ROOT_PATH . '/core/Middleware.php';

// Autoload classes with proper case sensitivity
spl_autoload_register(function ($class) {
    // Xử lý namespace case-insensitive để tìm file
    $basePath = ROOT_PATH;

    // Fix cho vấn đề Core vs core
    if (strpos($class, 'Core\\') === 0) {
        // Thay thế Core\ bằng core/ trong đường dẫn
        $classPath = str_replace('Core\\', 'core/', $class) . '.php';
        $filePath = $basePath . '/' . $classPath;

        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        }
    }

    // Fix cho vấn đề case sensitivity trên Linux
    $parts = explode('\\', $class);
    $className = array_pop($parts);
    $namespace = implode('\\', $parts);

    // Chuẩn hóa namespace
    $normNamespace = '';
    if (
        strtolower($namespace) === strtolower(NAMESPACE_CONTROLLERS) ||
        strpos(strtolower($namespace), strtolower(NAMESPACE_CONTROLLERS) . '\\') === 0
    ) {
        $normNamespace = NAMESPACE_CONTROLLERS;
    } elseif (
        strtolower($namespace) === strtolower(NAMESPACE_MODELS) ||
        strpos(strtolower($namespace), strtolower(NAMESPACE_MODELS) . '\\') === 0
    ) {
        $normNamespace = NAMESPACE_MODELS;
    } elseif (
        strtolower($namespace) === strtolower(NAMESPACE_MIDDLEWARE) ||
        strpos(strtolower($namespace), strtolower(NAMESPACE_MIDDLEWARE) . '\\') === 0
    ) {
        $normNamespace = NAMESPACE_MIDDLEWARE;
    } else {
        $normNamespace = $namespace;
    }

    // Xử lý đường dẫn file
    if ($normNamespace === NAMESPACE_CONTROLLERS) {
        $classPath = 'app/controllers/' . $className . '.php';
    } elseif ($normNamespace === NAMESPACE_MODELS) {
        $classPath = 'app/models/' . $className . '.php';
    } elseif ($normNamespace === NAMESPACE_MIDDLEWARE) {
        $classPath = 'app/middleware/' . $className . '.php';
    } else {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }

    $filePath = $basePath . DIRECTORY_SEPARATOR . $classPath;

    error_log("Attempting to load: " . $filePath);

    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }

    // Thử tìm file trong các case khác nhau
    $directories = explode(DIRECTORY_SEPARATOR, dirname($filePath));
    $filename = basename($filePath);

    // Thử các biến thể của tên file
    $variations = [
        $filename,
        strtolower($filename),
        ucfirst(strtolower($filename))
    ];

    foreach ($directories as $dir) {
        $currentPath = $basePath . DIRECTORY_SEPARATOR . $dir;
        if (is_dir($currentPath)) {
            $files = scandir($currentPath);
            foreach ($files as $file) {
                foreach ($variations as $variation) {
                    if (strtolower($file) === strtolower($variation)) {
                        $correctPath = $currentPath . DIRECTORY_SEPARATOR . $file;
                        error_log("Found match: " . $correctPath);
                        require_once $correctPath;
                        return true;
                    }
                }
            }
        }
    }

    error_log("Could not load class: $class, tried path: $filePath");
    return false;
});

try {
    // Load routes
    require_once CONFIG_PATH . '/routes.php';

    $router = new Core\Router();

    // Debug logging of router class
    error_log("Router class: " . get_class($router));

    // Register routes
    foreach ($routes as $path => $handler) {
        $router->addRoute($path, $handler);
    }

    // Register middleware
    foreach ($middlewares as $path => $middleware) {
        $router->addMiddleware($path, $middleware);
    }

    // Dispatch request
    $router->dispatch();
} catch (Exception $e) {
    // Gọi ErrorHandler xử lý lỗi
    error_log("ROUTER ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    Core\ErrorHandler::handleException($e);
}
