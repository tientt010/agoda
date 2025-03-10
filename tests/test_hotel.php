<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../core/BaseModel.php';
require_once '../app/models/Hotel.php';

try {
    // Khởi tạo model Hotel
    $hotelModel = new \App\Models\Hotel();

    // Test 1: Tìm kiếm khách sạn
    echo "<h3>Test tìm kiếm khách sạn:</h3>";
    $filters = [
        'city' => 'Hồ Chí Minh',
        'star_rating' => 5,
        'price_min' => 1000000,
        'price_max' => 3000000
    ];
    $sort = ['field' => 'star_rating', 'direction' => 'DESC'];

    $hotels = $hotelModel->search($filters, $sort);
    if ($hotels) {
        echo "✅ Tìm thấy " . count($hotels) . " khách sạn<br>";
        echo "<pre>" . print_r($hotels, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy khách sạn phù hợp<br>";
    }

    // Test 2: Lấy chi tiết khách sạn
    echo "<h3>Test xem chi tiết khách sạn:</h3>";
    $hotelId = 1; // Giả sử ID = 1
    $hotelDetail = $hotelModel->getDetails($hotelId);
    if ($hotelDetail) {
        echo "✅ Lấy thông tin chi tiết thành công:<br>";
        echo "Tên khách sạn: " . $hotelDetail['name'] . "<br>";
        echo "Số phòng có sẵn: " . count($hotelDetail['rooms']) . "<br>";
        echo "Số tiện ích: " . count($hotelDetail['amenities']) . "<br>";
        echo "<pre>" . print_r($hotelDetail, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy thông tin khách sạn<br>";
    }

    // Test 3: Kiểm tra phòng trống
    echo "<h3>Test kiểm tra phòng trống:</h3>";
    $checkIn = '2024-03-20';
    $checkOut = '2024-03-22';
    $availableRooms = $hotelModel->getAvailableRooms($hotelId, $checkIn, $checkOut);
    if ($availableRooms) {
        echo "✅ Tìm thấy " . count($availableRooms) . " phòng trống:<br>";
        echo "<pre>" . print_r($availableRooms, true) . "</pre><br>";
    } else {
        echo "❌ Không có phòng trống trong thời gian này<br>";
    }

    // Test 4: Thêm tiện ích cho khách sạn
    echo "<h3>Test thêm tiện ích:</h3>";
    $amenityIds = [8, 9, 10]; // Giả sử có 3 tiện ích
    if ($hotelModel->addAmenities($hotelId, $amenityIds)) {
        echo "✅ Thêm tiện ích thành công<br>";
    } else {
        echo "❌ Thêm tiện ích thất bại. Lỗi: <pre>" . print_r($hotelModel->getErrors(), true) . "</pre><br>";
    }

    // Test 5: Lấy thống kê đặt phòng
    echo "<h3>Test thống kê đặt phòng:</h3>";
    $startDate = '2024-01-01';
    $endDate = '2024-12-31';
    $stats = $hotelModel->getBookingStats($hotelId, $startDate, $endDate);
    if ($stats) {
        echo "✅ Thống kê đặt phòng:<br>";
        echo "Tổng số đặt phòng: " . $stats['total_bookings'] . "<br>";
        echo "Đặt phòng đã xác nhận: " . $stats['confirmed_bookings'] . "<br>";
        echo "Đặt phòng đã hủy: " . $stats['cancelled_bookings'] . "<br>";
        echo "Tổng doanh thu: " . number_format($stats['total_revenue']) . " VND<br>";
        echo "Tổng số phòng: " . $stats['total_rooms'] . "<br>";
        echo "Thời gian lưu trú trung bình: " . $stats['avg_stay_duration'] . " ngày<br>";
        echo "Tỷ lệ đặt phòng thành công: " . $stats['booking_rate'] . "%<br>";
    } else {
        echo "❌ Không thể lấy thống kê. Lỗi: <pre>" . print_r($hotelModel->getErrors(), true) . "</pre><br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
