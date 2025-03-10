<?php

namespace Core;

/**
 * BaseController - Class cơ sở cho tất cả controllers:
 * - Quản lý views và layouts
 * - Xử lý session
 * - Flash messages
 * - Input validation
 * - Error handling
 * - Điều hướng và chuyển trang
 * - Response handling (JSON, HTML)
 */
class BaseController
{
    protected $viewPath = 'app/views/';
    protected $layout = 'layouts/main';

    public function __construct()
    {

        try {
            if (session_status() === PHP_SESSION_NONE) {
                if (!session_start()) {
                    throw new \Exception("Could not start session");
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            throw new \Exception("Session initialization failed");
        }
    }

    protected function view($view, $data = [])
    {
        try {
            // Extract data thành các biến riêng lẻ
            extract($data);

            // Bắt đầu output buffering
            ob_start();

            // Load view file với error handling
            $viewFile = $this->viewPath . $view . '.php';
            if (!file_exists($viewFile)) {
                throw new \Exception("View file not found: {$viewFile}");
            }
            require $viewFile;

            // Lấy nội dung view
            $content = ob_get_clean();

            // Kiểm tra và load layout
            $layoutFile = $this->viewPath . $this->layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout file not found: {$layoutFile}");
            }
            require $layoutFile;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            if (DEBUG_MODE) {
                throw $e;
            } else {
                $this->renderError('An error occurred while rendering the view');
            }
        }
    }

    // Chuyển hướng đến URL khác
    protected function redirect($url, $statusCode = 303)
    {
        if (!defined('SITE_URL')) {
            throw new \Exception("SITE_URL is not defined");
        }
        header('Location: ' . SITE_URL . $url, true, $statusCode);
        exit;
    }

    // Trả về JSON response
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Set flash message
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    // Get flash message
    protected function getFlash($type)
    {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    // Validate input data
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            // Required validation
            if (strpos($rule, 'required') !== false && empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }

            // Email validation
            if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email format';
            }

            // Add more validation rules as needed
        }

        return $errors;
    }

    protected function renderError($message)
    {
        if (DEBUG_MODE) {
            header("HTTP/1.1 500 Internal Server Error");
            echo "<h1>Error</h1>";
            echo "<p>{$message}</p>";
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            include $this->viewPath . 'errors/500.php';
        }
        exit;
    }

    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function getPost($key = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    protected function getQuery($key = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
}
