<?php

namespace App\Middleware;

use Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle($next)
    {
        // Debug logging
        error_log("AuthMiddleware triggered. User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));
        error_log("Current URI: " . $_SERVER['REQUEST_URI']);
        error_log("SITE_URL: " . SITE_URL);
        error_log("Full session data: " . print_r($_SESSION, true));

        // Kiểm tra người dùng đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Lưu URL hiện tại để redirect sau khi đăng nhập
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

            error_log("User not logged in, redirecting to login with redirect_url set to: " . $_SESSION['redirect_url']);
            header('Location: ' . SITE_URL . '/login');
            exit;
        }

        error_log("Auth check passed, continuing to controller...");

        // Ensure we return the next middleware's response
        $response = $next();
        return $response;
    }
}
