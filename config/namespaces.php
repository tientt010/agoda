<?php

/**
 * File cấu hình namespace
 * Điều này giúp đảm bảo tính thống nhất của namespace trong toàn bộ dự án
 */

// Định nghĩa namespace theo chuẩn PSR-4
// Namespace gốc cho các thành phần chính
define('NAMESPACE_APP', 'App');
define('NAMESPACE_CORE', 'Core');
define('NAMESPACE_CONFIG', 'Config');

// Namespace cho các thành phần con
define('NAMESPACE_CONTROLLERS', NAMESPACE_APP . '\\Controllers');
define('NAMESPACE_MODELS', NAMESPACE_APP . '\\Models');
define('NAMESPACE_MIDDLEWARE', NAMESPACE_APP . '\\Middleware');

// Định nghĩa đường dẫn file tương ứng với namespace - kiểm tra trước khi định nghĩa
if (!defined('CONTROLLERS_PATH')) {
    define('CONTROLLERS_PATH', ROOT_PATH . '/app/controllers');
}

if (!defined('MODELS_PATH')) {
    define('MODELS_PATH', ROOT_PATH . '/app/models');
}

if (!defined('MIDDLEWARE_PATH')) {
    define('MIDDLEWARE_PATH', ROOT_PATH . '/app/middleware');
}

// Hàm helper chuyển namespace thành đường dẫn file
function namespaceToPath($namespace)
{
    return str_replace('\\', '/', $namespace) . '.php';
}
