<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../core/BaseModel.php';  // Thêm dòng này
require_once '../app/models/User.php';

try {
    // Khởi tạo model User
    $userModel = new \App\Models\User();

    // Test 1: Đăng ký user mới
    echo "<h3>Test đăng ký user:</h3>";
    $userData = [
        'username' => 'test_user2',
        'email' => 'test2@example.com',
        'password' => 'password123',
        'full_name' => 'Test User',
        'phone' => '0123456789'
    ];

    $userId = $userModel->register($userData);
    if ($userId) {
        echo "✅ Đăng ký thành công. User ID: $userId<br>";
    } else {
        echo "❌ Đăng ký thất bại. Lỗi: <pre>" . print_r($userModel->getErrors(), true) . "</pre><br>";
    }

    // Test 2: Kiểm tra đăng nhập
    echo "<h3>Test đăng nhập:</h3>";
    $auth = $userModel->authenticate('test@example.com', 'password123');
    if ($auth) {
        echo "✅ Đăng nhập thành công. User info: <pre>" . print_r($auth, true) . "</pre><br>";
    } else {
        echo "❌ Đăng nhập thất bại. Lỗi: <pre>" . print_r($userModel->getErrors(), true) . "</pre><br>";
    }

    // Test 3: Tìm user theo email
    echo "<h3>Test tìm user theo email:</h3>";
    $user = $userModel->findByEmail('test@example.com');
    if ($user) {
        echo "✅ Tìm thấy user: <pre>" . print_r($user, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy user<br>";
    }

    // Test 4: Cập nhật thông tin user
    echo "<h3>Test cập nhật profile:</h3>";
    $updateData = [
        'full_name' => 'Updated Test User',
        'phone' => '9876543210'
    ];

    if ($userModel->updateProfile($userId, $updateData)) {
        echo "✅ Cập nhật thành công<br>";
        $updatedUser = $userModel->find($userId);
        echo "User sau khi cập nhật: <pre>" . print_r($updatedUser, true) . "</pre><br>";
    } else {
        echo "❌ Cập nhật thất bại. Lỗi: <pre>" . print_r($userModel->getErrors(), true) . "</pre><br>";
    }

    // Test 5: Đổi mật khẩu
    echo "<h3>Test đổi mật khẩu:</h3>";
    if ($userModel->changePassword($userId, 'password123', 'newpassword123')) {
        echo "✅ Đổi mật khẩu thành công<br>";
    } else {
        echo "❌ Đổi mật khẩu thất bại. Lỗi: <pre>" . print_r($userModel->getErrors(), true) . "</pre><br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
