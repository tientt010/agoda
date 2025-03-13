<?php

namespace App\Middleware;

use Core\Middleware;

class AdminMiddleware extends Middleware
{
    public function handle($next)
    {
        // Debug logging
        error_log("AdminMiddleware triggered. User role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Not set'));

        // Kiểm tra người dùng có quyền admin không
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            error_log("Access denied: User is not admin");
            header('Location: ' . SITE_URL . '/login');
            exit;
        }

        error_log("Admin check passed, continuing...");

        // Chuyển tiếp cho middleware tiếp theo hoặc controller
        $response = $next();
        return $response;
    }
}
