<?php

namespace App\Models;

use Core\BaseModel;

/**
 * Model User - Xử lý các tác vụ người dùng:
 * - Đăng ký tài khoản mới
 * - Xác thực đăng nhập
 * - Quản lý thông tin cá nhân
 * - Đổi mật khẩu
 * - Xem lịch sử đặt phòng
 * - Phân quyền người dùng
 */
class User extends BaseModel
{
    // Định nghĩa tên bảng
    protected $table = 'users';

    // Cập nhật danh sách các trường có thể cập nhật
    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'phone',
        'address',
        'birth_date',
        'gender',
        'avatar'
    ];

    // Định nghĩa rules cho validation
    protected $rules = [
        'username' => 'required|min:3|max:50',
        'email' => 'required|email',
        'password' => 'required|min:6',
        'full_name' => 'required|max:100',
        'phone' => 'max:20',
        'address' => 'max:255',
        'gender' => 'in:male,female,other'
    ];

    /**
     * Tìm user theo email
     * @param string $email Email cần tìm
     * @return array|false Thông tin user hoặc false nếu không tìm thấy
     */
    public function findByEmail($email)
    {
        return $this->where('email', $email)->get()[0] ?? false;
    }

    /**
     * Đăng ký user mới
     * @param array $data Dữ liệu đăng ký
     * @return int|false ID của user mới hoặc false nếu thất bại
     */
    public function register($data)
    {
        // Validate dữ liệu
        if (!$this->validate($data, $this->rules)) {
            return false;
        }

        // Kiểm tra email đã tồn tại
        if ($this->findByEmail($data['email'])) {
            $this->errors['email'] = 'Email đã được sử dụng';
            return false;
        }

        // Mã hóa mật khẩu
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Tạo user mới
        return $this->create($data);
    }

    /**
     * Xác thực đăng nhập
     * @param string $email Email đăng nhập
     * @param string $password Mật khẩu
     * @return array|false Thông tin user hoặc false nếu thất bại
     */
    public function authenticate($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            $this->errors['email'] = 'Email không tồn tại';
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $this->errors['password'] = 'Mật khẩu không đúng';
            return false;
        }

        // Loại bỏ mật khẩu trước khi trả về
        unset($user['password']);
        return $user;
    }

    /**
     * Xác thực theo loại thông tin đăng nhập
     */
    public function authenticateByType($loginId, $password, $type)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ";

        switch ($type) {
            case 'phone':
                $sql .= "phone = ?";
                break;
            case 'email':
                $sql .= "email = ?";
                break;
            default:
                $sql .= "username = ?";
                break;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$loginId]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->errors['login'] = 'Tài khoản không tồn tại';
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            $this->errors['password'] = 'Mật khẩu không đúng';
            return false;
        }

        unset($user['password']);
        return $user;
    }

    /**
     * Cập nhật thông tin user
     * @param int $id ID của user
     * @param array $data Dữ liệu cần cập nhật
     * @return bool Kết quả cập nhật
     */
    public function updateProfile($id, $data)
    {
        // Loại bỏ các trường không được phép cập nhật
        unset($data['password']); // Password phải được cập nhật riêng
        unset($data['username']); // Username không được thay đổi

        // Kiểm tra email đã tồn tại chưa nếu có thay đổi
        if (isset($data['email']) && !empty($data['email'])) {
            $currentUser = $this->find($id);

            if ($currentUser['email'] !== $data['email']) {
                // Kiểm tra email mới đã tồn tại chưa
                $existingUser = $this->where('email', $data['email'])->get();
                if (!empty($existingUser)) {
                    $this->errors['email'] = 'Email này đã được sử dụng bởi tài khoản khác';
                    return false;
                }
            }
        }

        // Validate dữ liệu
        $rules = array_intersect_key($this->rules, $data);
        if (!$this->validate($data, $rules)) {
            return false;
        }

        // Xử lý ngày trống
        if (empty($data['birth_date'])) {
            $data['birth_date'] = null;
        }

        return $this->update($id, $data);
    }

    /**
     * Đổi mật khẩu
     * @param int $id ID của user
     * @param string $currentPassword Mật khẩu hiện tại
     * @param string $newPassword Mật khẩu mới
     * @return bool Kết quả thay đổi mật khẩu
     */
    public function changePassword($id, $currentPassword, $newPassword)
    {
        $user = $this->find($id);

        if (!password_verify($currentPassword, $user['password'])) {
            $this->errors['current_password'] = 'Mật khẩu hiện tại không đúng';
            return false;
        }

        if (strlen($newPassword) < 6) {
            $this->errors['new_password'] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
            return false;
        }

        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Lấy lịch sử đặt phòng của user
     * @param int $userId ID của user
     * @return array Danh sách các booking
     */
    public function getBookingHistory($userId)
    {
        $sql = "SELECT b.*, r.room_number, h.name as hotel_name 
                FROM bookings b 
                JOIN rooms r ON b.room_id = r.id 
                JOIN hotels h ON r.hotel_id = h.id 
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách khách sạn yêu thích của user
     * @param int $userId
     * @return array
     */
    public function getFavoriteHotels($userId)
    {
        $sql = "SELECT h.*, f.created_at as favorited_at
                FROM hotels h
                INNER JOIN favorites f ON h.id = f.hotel_id 
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Cập nhật avatar của user
     * @param int $id ID của user
     * @param string $fileName Tên file avatar mới
     * @return bool Kết quả cập nhật
     */
    public function updateAvatar($id, $fileName)
    {
        try {
            // Lấy avatar hiện tại
            $currentUser = $this->find($id);
            $currentAvatar = $currentUser['avatar'];

            // Cập nhật avatar mới trong database
            $result = $this->update($id, ['avatar' => $fileName]);

            // Xóa avatar cũ nếu không phải avatar mặc định
            if ($result && $currentAvatar != 'default.jpg') {
                $avatarPath = ROOT_PATH . '/public/images/avatars/' . $currentAvatar;
                if (file_exists($avatarPath)) {
                    unlink($avatarPath);
                }
            }

            return $result;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
}
