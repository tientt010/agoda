<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\User;
use App\Models\Booking;

class UserController extends BaseController
{
    private $userModel;
    private $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    public function profile()
    {
        try {
            // Lấy thông tin user từ session
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->find($userId);

            if (!$user) {
                throw new \Exception('Không tìm thấy thông tin người dùng');
            }

            // Lấy lịch sử đặt phòng, giới hạn 5 booking gần nhất
            $recentBookings = $this->bookingModel->search([
                'user_id' => $userId,
                'limit' => 5
            ]);

            // Lấy khách sạn yêu thích
            $favoriteHotels = $this->userModel->getFavoriteHotels($userId);

            // Render view với dữ liệu
            return $this->view('user/profile', [
                'user' => $user,
                'recentBookings' => $recentBookings,
                'favoriteHotels' => $favoriteHotels,
                'pageTitle' => 'Thông tin tài khoản'
            ]);
        } catch (\Exception $e) {
            // Log lỗi và hiển thị thông báo
            error_log($e->getMessage());
            $this->setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            return $this->redirect('/');
        }
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function updateProfile()
    {
        if (!$this->isPost()) {
            return $this->redirect('/user/profile');
        }

        $userId = $_SESSION['user_id'];

        // Lấy dữ liệu từ form
        $data = [
            'full_name' => $this->getPost('full_name'),
            'email' => $this->getPost('email'),
            'phone' => $this->getPost('phone'),
            'address' => $this->getPost('address'),
            'birth_date' => $this->getPost('birth_date'),
            'gender' => $this->getPost('gender')
        ];

        // Cập nhật thông tin
        $success = $this->userModel->updateProfile($userId, $data);

        if ($success) {
            // Cập nhật session nếu cần
            $_SESSION['user_name'] = $data['full_name'];

            $this->setFlash('success', 'Cập nhật thông tin thành công!');
        } else {
            $this->setFlash('error', 'Cập nhật thông tin thất bại: ' . reset($this->userModel->getErrors()));
        }

        return $this->redirect('/user/profile');
    }

    /**
     * Cập nhật mật khẩu
     */
    public function updatePassword()
    {
        if (!$this->isPost()) {
            return $this->redirect('/user/profile');
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $this->getPost('current_password');
        $newPassword = $this->getPost('new_password');
        $confirmPassword = $this->getPost('confirm_password');

        // Kiểm tra mật khẩu xác nhận
        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Mật khẩu xác nhận không khớp');
            return $this->redirect('/user/profile');
        }

        // Đổi mật khẩu
        $success = $this->userModel->changePassword($userId, $currentPassword, $newPassword);

        if ($success) {
            $this->setFlash('success', 'Đổi mật khẩu thành công!');
        } else {
            $this->setFlash('error', 'Đổi mật khẩu thất bại: ' . reset($this->userModel->getErrors()));
        }

        return $this->redirect('/user/profile');
    }

    /**
     * Cập nhật avatar
     */
    public function updateAvatar()
    {
        if (!$this->isPost()) {
            return $this->redirect('/user/profile');
        }

        $userId = $_SESSION['user_id'];

        try {
            // Kiểm tra file upload
            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Không thể tải lên ảnh đại diện: ' . $this->getUploadErrorMessage($_FILES['avatar']['error']));
            }

            $file = $_FILES['avatar'];

            // Kiểm tra loại file
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new \Exception('File tải lên không phải là ảnh hợp lệ');
            }

            $mimeType = $imageInfo['mime'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($mimeType, $allowedTypes)) {
                throw new \Exception('Chỉ chấp nhận file ảnh định dạng JPG, PNG, GIF');
            }

            // Kiểm tra kích thước file (tối đa 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new \Exception('Kích thước file không được vượt quá 2MB');
            }

            // Tạo thư mục lưu trữ nếu chưa tồn tại
            $uploadDir = ROOT_PATH . '/public/images/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Đặt tên file mới để tránh trùng lặp
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;

            // Di chuyển file từ tmp lên thư mục đích
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new \Exception('Không thể lưu file ảnh. Vui lòng thử lại sau.');
            }

            // Cập nhật đường dẫn avatar trong database
            $result = $this->userModel->updateAvatar($userId, $fileName);

            if (!$result) {
                // Xóa file đã upload nếu cập nhật database thất bại
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                throw new \Exception('Không thể cập nhật ảnh đại diện trong cơ sở dữ liệu');
            }

            $this->setFlash('success', 'Cập nhật ảnh đại diện thành công');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        return $this->redirect('/user/profile');
    }

    /**
     * Lấy thông báo lỗi upload dựa trên mã lỗi
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá upload_max_filesize trong php.ini)',
            UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt quá MAX_FILE_SIZE trong form HTML)',
            UPLOAD_ERR_PARTIAL => 'Upload chưa hoàn thành',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thư mục tạm không tồn tại',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file vào ổ đĩa',
            UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension',
        ];

        return isset($errors[$errorCode]) ? $errors[$errorCode] : 'Lỗi không xác định';
    }

    public function getStatusBadgeClass($status)
    {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'confirmed':
                return 'success';
            case 'cancelled':
                return 'danger';
            case 'completed':
                return 'info';
            default:
                return 'secondary';
        }
    }

    public function getStatusText($status)
    {
        switch ($status) {
            case 'pending':
                return 'Chờ xác nhận';
            case 'confirmed':
                return 'Đã xác nhận';
            case 'cancelled':
                return 'Đã hủy';
            case 'completed':
                return 'Hoàn thành';
            default:
                return 'Không xác định';
        }
    }
}
