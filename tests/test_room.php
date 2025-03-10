<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../core/BaseModel.php';
require_once '../app/models/Room.php';

try {
    // Khởi tạo model Room
    $roomModel = new \App\Models\Room();

    // Test 1: Tạo phòng mới
    echo "<h3>Test tạo phòng mới:</h3>";
    $roomData = [
        'hotel_id' => 1,
        'room_type_id' => 1,
        'room_number' => 'A101',
        'price_per_night' => 1000000,
        'status' => \App\Models\Room::STATUS_AVAILABLE
    ];

    $roomId = $roomModel->create($roomData);
    if ($roomId) {
        echo "✅ Tạo phòng thành công. Room ID: $roomId<br>";
    } else {
        echo "❌ Tạo phòng thất bại. Lỗi: <pre>" . print_r($roomModel->getErrors(), true) . "</pre><br>";
    }

    // Test 2: Kiểm tra phòng trống
    echo "<h3>Test kiểm tra phòng trống:</h3>";
    $roomModel->load($roomId);
    $checkIn = date('Y-m-d', strtotime('+1 day'));
    $checkOut = date('Y-m-d', strtotime('+3 days'));

    if ($roomModel->isAvailable($checkIn, $checkOut)) {
        echo "✅ Phòng có sẵn trong khoảng thời gian yêu cầu<br>";
    } else {
        echo "❌ Phòng không có sẵn. Lỗi: <pre>" . print_r($roomModel->getErrors(), true) . "</pre><br>";
    }

    // Test 3: Tìm phòng theo loại
    echo "<h3>Test tìm phòng theo loại:</h3>";
    $availableRooms = $roomModel->findAvailableByType(1, $checkIn, $checkOut);
    if ($availableRooms) {
        echo "✅ Tìm thấy " . count($availableRooms) . " phòng trống:<br>";
        echo "<pre>" . print_r($availableRooms, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy phòng trống<br>";
    }

    // Test 4: Tính giá phòng
    echo "<h3>Test tính giá phòng:</h3>";
    $totalPrice = $roomModel->calculateTotalPrice($checkIn, $checkOut);
    if ($totalPrice) {
        echo "✅ Tổng giá phòng: " . number_format($totalPrice) . " VND<br>";
    } else {
        echo "❌ Không thể tính giá. Lỗi: <pre>" . print_r($roomModel->getErrors(), true) . "</pre><br>";
    }

    // Test 5: Cập nhật trạng thái
    echo "<h3>Test cập nhật trạng thái:</h3>";
    if ($roomModel->updateStatus(\App\Models\Room::STATUS_MAINTENANCE)) {
        echo "✅ Cập nhật trạng thái thành công<br>";
    } else {
        echo "❌ Cập nhật thất bại. Lỗi: <pre>" . print_r($roomModel->getErrors(), true) . "</pre><br>";
    }

    // Test 6: Lấy thông tin chi tiết
    echo "<h3>Test thông tin chi tiết:</h3>";
    $hotelDetails = $roomModel->getHotelDetails();
    $roomTypeDetails = $roomModel->getRoomTypeDetails();

    if ($hotelDetails && $roomTypeDetails) {
        echo "✅ Thông tin khách sạn:<br>";
        echo "<pre>" . print_r($hotelDetails, true) . "</pre><br>";
        echo "✅ Thông tin loại phòng:<br>";
        echo "<pre>" . print_r($roomTypeDetails, true) . "</pre><br>";
    } else {
        echo "❌ Không thể lấy thông tin chi tiết. Lỗi: <pre>" . print_r($roomModel->getErrors(), true) . "</pre><br>";
    }

    // Test 7: Lấy thống kê phòng
    echo "<h3>Test thống kê phòng:</h3>";
    $stats = $roomModel->getRoomStats(1);
    if ($stats) {
        echo "✅ Thống kê phòng của khách sạn:<br>";
        echo "Tổng số phòng: " . $stats['total_rooms'] . "<br>";
        echo "Số phòng trống: " . $stats['available_rooms'] . "<br>";
        echo "Số phòng đã đặt: " . $stats['booked_rooms'] . "<br>";
        echo "Số phòng bảo trì: " . $stats['maintenance_rooms'] . "<br>";
        echo "Giá thấp nhất: " . number_format($stats['min_price']) . " VND<br>";
        echo "Giá cao nhất: " . number_format($stats['max_price']) . " VND<br>";
        echo "Giá trung bình: " . number_format($stats['avg_price']) . " VND<br>";
    } else {
        echo "❌ Không thể lấy thống kê<br>";
    }

    // Test 8: Kiểm tra khả năng bảo trì
    echo "<h3>Test khả năng bảo trì:</h3>";
    if ($roomModel->canMaintenance()) {
        echo "✅ $roomModel->id Phòng có thể bảo trì<br>";
    } else {
        echo "❌ Phòng không thể bảo trì do có booking<br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
