<?php

namespace App\Middleware;

use Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle($next)
    {
        // Kiểm tra người dùng đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            // Lưu URL hiện tại để redirect sau khi đăng nhập
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . SITE_URL . '/login');
            exit;
        }

        // Tiếp tục chuỗi middleware
        return $next();
    }
}
