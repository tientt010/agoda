<?php

/**
 * Entry point của ứng dụng:
 * - Khởi tạo cấu hình 
 * - Xử lý autoload classes
 * - Định tuyến URL
 * - Khởi chạy middleware
 * - Error handling tổng thể
 * - Debug mode
 */
require_once 'config/config.php';
require_once 'config/Database.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);

// Autoload classes
spl_autoload_register(function ($class) {
    // Chuyển namespace path thành file path
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';

    // Kiểm tra file tồn tại
    if (file_exists($file)) {
        require_once $file;
        return true;
    }

    // Log lỗi nếu không tìm thấy file
    error_log("Could not load class: $class from $file");
    return false;
});

try {
    // Initialize Router
    $router = new Core\Router();

    // Define routes
    $router->addRoute('/', 'HomeController@index');

    // Auth routes - cập nhật pattern để match chính xác
    $router->addRoute('/login/?$', 'AuthController@login');
    $router->addRoute('/register/?$', 'AuthController@register');
    $router->addRoute('/logout/?$', 'AuthController@logout');
    $router->addRoute('/forgot-password', 'AuthController@forgotPassword');

    // Protected routes
    $router->addMiddleware('/profile', 'AuthMiddleware');
    $router->addMiddleware('/booking/(\d+)', 'AuthMiddleware');

    $router->addRoute('/hotels', 'HotelController@index');
    $router->addRoute('/hotels/(\d+)', 'HotelController@show');
    $router->addRoute('/booking/(\d+)', 'BookingController@create');
    $router->addRoute('/user/profile', 'UserController@profile');

    // Dispatch the request
    $router->dispatch();
} catch (Exception $e) {
    if (DEBUG_MODE) {
        echo "<h1>Error</h1>";
        echo "<p>{$e->getMessage()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    } else {
        // Log error
        error_log($e->getMessage());
        // Show friendly error page
        include 'app/views/errors/500.php';
    }
}
