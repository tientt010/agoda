<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../core/BaseModel.php';
require_once '../app/models/Room.php';
require_once '../app/models/Booking.php';

try {
    // Khởi tạo model Booking
    $bookingModel = new \App\Models\Booking();

    // Test 1: Tạo booking mới
    echo "<h3>Test tạo booking:</h3>";
    $bookingData = [
        'user_id' => 1,
        'room_id' => 1,
        'check_in_date' => date('Y-m-d', strtotime('+1 day')),
        'check_out_date' => date('Y-m-d', strtotime('+3 days')),
        'total_price' => 2000000,
        'status' => \App\Models\Booking::STATUS_PENDING,
        'payment_status' => \App\Models\Booking::PAYMENT_UNPAID,
        'special_requests' => 'Extra pillow'
    ];

    $bookingId = $bookingModel->create($bookingData);
    if ($bookingId) {
        echo "✅ Tạo booking thành công. Booking ID: $bookingId<br>";
    } else {
        echo "❌ Tạo booking thất bại. Lỗi: <pre>" . print_r($bookingModel->getErrors(), true) . "</pre><br>";
    }

    // Test 2: Lấy chi tiết booking
    echo "<h3>Test xem chi tiết booking:</h3>";
    $bookingDetail = $bookingModel->getDetails($bookingId);
    if ($bookingDetail) {
        echo "✅ Chi tiết booking:<br>";
        echo "<pre>" . print_r($bookingDetail, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy booking<br>";
    }

    // Test 3: Tìm kiếm booking
    echo "<h3>Test tìm kiếm booking:</h3>";
    $filters = [
        'user_id' => 1,
        'status' => \App\Models\Booking::STATUS_PENDING
    ];
    $bookings = $bookingModel->search($filters);
    if ($bookings) {
        echo "✅ Tìm thấy " . count($bookings) . " booking:<br>";
        echo "<pre>" . print_r($bookings, true) . "</pre><br>";
    } else {
        echo "❌ Không tìm thấy booking nào<br>";
    }

    // Test 4: Xác nhận thanh toán
    echo "<h3>Test xác nhận thanh toán:</h3>";
    if ($bookingModel->confirmPayment($bookingId)) {
        echo "✅ Xác nhận thanh toán thành công<br>";
        $updatedBooking = $bookingModel->find($bookingId);
        echo "Trạng thái mới: " . $updatedBooking['status'] . " - " . $updatedBooking['payment_status'] . "<br>";
    } else {
        echo "❌ Xác nhận thanh toán thất bại. Lỗi: <pre>" . print_r($bookingModel->getErrors(), true) . "</pre><br>";
    }

    // Test 5: Hủy booking
    echo "<h3>Test hủy booking:</h3>";
    if ($bookingModel->cancel($bookingId)) {
        echo "✅ Hủy booking thành công<br>";
        $cancelledBooking = $bookingModel->find($bookingId);
        echo "Trạng thái sau khi hủy: " . $cancelledBooking['status'] . " - " . $cancelledBooking['payment_status'] . "<br>";
    } else {
        echo "❌ Hủy booking thất bại. Lỗi: <pre>" . print_r($bookingModel->getErrors(), true) . "</pre><br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
