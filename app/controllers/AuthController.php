<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;

class AuthController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function login()
    {
        // Nếu đã đăng nhập thì redirect về trang chủ
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/');
        }

        if ($this->isPost()) {
            $loginId = $this->getPost('login_id');
            $password = $this->getPost('password');

            // Xác định loại thông tin đăng nhập
            $loginType = $this->determineLoginType($loginId);

            $user = $this->userModel->authenticateByType($loginId, $password, $loginType);

            if ($user) {
                // Lưu thông tin user vào session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];

                // Kiểm tra có URL redirect không
                if (isset($_SESSION['redirect_url'])) {
                    $redirectUrl = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    return $this->redirect($redirectUrl);
                }

                // Redirect về trang chủ
                return $this->redirect('/');
            }

            // Lưu lỗi để hiển thị
            $this->setFlash('error', 'Thông tin đăng nhập không chính xác');
        }

        return $this->view('auth/login');
    }

    /**
     * Xác định loại thông tin đăng nhập
     */
    private function determineLoginType($loginId)
    {
        if (is_numeric($loginId)) {
            return 'phone';
        }
        if (filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        return 'username';
    }

    /**
     * Xử lý đăng ký tài khoản mới
     */
    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            return $this->redirect('/');
        }

        if ($this->isPost()) {
            $data = [
                'username' => $this->getPost('username'),
                'email' => $this->getPost('email'),
                'password' => $this->getPost('password'),
                'full_name' => $this->getPost('full_name'),
                'phone' => $this->getPost('phone'),
                'address' => $this->getPost('address')
            ];

            // Validate password confirmation
            if ($this->getPost('password') !== $this->getPost('password_confirmation')) {
                $this->setFlash('error', 'Mật khẩu xác nhận không khớp');
                return $this->view('auth/register', ['data' => $data]);
            }

            $userId = $this->userModel->register($data);

            if ($userId) {
                $this->setFlash('success', 'Đăng ký thành công. Vui lòng đăng nhập.');
                return $this->redirect('/login');
            }

            // Lưu lỗi để hiển thị
            $this->setFlash('error', reset($this->userModel->getErrors()));
            return $this->view('auth/register', ['data' => $data]);
        }

        return $this->view('auth/register');
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout()
    {
        // Xóa hết session
        session_destroy();

        // Redirect về trang đăng nhập
        return $this->redirect('/login');
    }

    /**
     * Xử lý quên mật khẩu
     */
    public function forgotPassword()
    {
        if ($this->isPost()) {
            $email = $this->getPost('email');
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                // Tạo token reset password
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Lưu token vào database
                $this->userModel->saveResetToken($user['id'], $token, $expires);

                // Gửi email reset password
                $resetLink = SITE_URL . "/reset-password?token=" . $token;
                // TODO: Implement email sending

                $this->setFlash('success', 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn');
                return $this->redirect('/login');
            }

            $this->setFlash('error', 'Email không tồn tại trong hệ thống');
        }

        return $this->view('auth/forgot-password');
    }
}
