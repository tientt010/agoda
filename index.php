<?php

// Error reporting first
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load paths config
require_once __DIR__ . '/config/paths.php';

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
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/Database.php';
require_once CORE_PATH . '/Router.php';
require_once CORE_PATH . '/BaseController.php';
require_once CORE_PATH . '/BaseModel.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Autoload classes with case sensitivity
spl_autoload_register(function ($class) {
    // Convert namespace to path and make it lowercase
    $path = strtolower(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class));

    $pathParts = explode(DIRECTORY_SEPARATOR, $path);
    $fileName = array_pop($pathParts);

    // Keep PascalCase for actual filenames
    if (strpos($fileName, 'controller') !== false) {
        $fileName = ucfirst(str_replace('controller', 'Controller', $fileName));
    } else if (strpos($path, 'models') !== false) {
        $fileName = ucfirst($fileName);
    }

    // Make the path lowercase
    $path = implode(DIRECTORY_SEPARATOR, array_map('strtolower', $pathParts));
    $path .= DIRECTORY_SEPARATOR . $fileName . '.php';

    $file = ROOT_PATH . DIRECTORY_SEPARATOR . $path;

    error_log("Trying to load: " . $file);

    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    error_log("Could not load class: $class from $file");
    return false;
});

try {
    $router = new Core\Router();

    // Debug URL processing
    $currentUrl = $_SERVER['REQUEST_URI'];
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    error_log("Current URL: " . $currentUrl);
    error_log("Script Name: " . $scriptName);

    // Define routes
    $routes = [
        '/' => 'HomeController@index',
        '/login' => 'AuthController@login',
        '/register' => 'AuthController@register',
        '/logout' => 'AuthController@logout',
        '/forgot-password' => 'AuthController@forgotPassword',
        '/profile' => 'UserController@profile',
        '/booking/(\d+)' => 'BookingController@create',
        '/hotels/(\d+)' => 'HotelController@show',
        '/hotels' => 'HotelController@index'
    ];

    // Register all routes
    foreach ($routes as $path => $handler) {
        $router->addRoute($path, $handler);
        error_log("Registered route: {$path} => {$handler}");
    }

    $router->dispatch();
} catch (Exception $e) {
    error_log("Main error: " . $e->getMessage());
    error_log($e->getTraceAsString());

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
