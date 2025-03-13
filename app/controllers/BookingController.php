<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Hotel;

class BookingController extends BaseController
{
    private $bookingModel;
    private $roomModel;
    private $hotelModel;

    public function __construct()
    {
        parent::__construct();
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
        $this->hotelModel = new Hotel();
    }

    /**
     * Hiển thị form đặt phòng
     */
    public function create($roomId)
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $this->setFlash('error', 'Vui lòng đăng nhập để đặt phòng');
            return $this->redirect('/login');
        }

        // Load thông tin phòng
        $this->roomModel->load($roomId);

        if (!$this->roomModel->id) {
            $this->setFlash('error', 'Không tìm thấy phòng');
            return $this->redirect('/hotels');
        }

        // Lấy thông tin khách sạn
        $hotel = $this->roomModel->getHotelDetails();

        // Lấy thông tin loại phòng
        $roomType = $this->roomModel->getRoomTypeDetails();

        // Lấy ngày check-in/check-out từ query
        $checkIn = $this->getQuery('check_in', date('Y-m-d', strtotime('+1 day')));
        $checkOut = $this->getQuery('check_out', date('Y-m-d', strtotime('+2 days')));

        // Kiểm tra phòng có sẵn không
        if (!$this->roomModel->isAvailable($checkIn, $checkOut)) {
            $this->setFlash('error', 'Phòng không khả dụng trong thời gian này');
            return $this->redirect('/hotels/' . $hotel['id']);
        }

        // Tính tổng giá
        $totalPrice = $this->roomModel->calculateTotalPrice($checkIn, $checkOut);

        if ($this->isPost()) {
            $bookingData = [
                'user_id' => $_SESSION['user_id'],
                'room_id' => $roomId,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'total_price' => $totalPrice,
                'status' => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'special_requests' => $this->getPost('special_requests')
            ];

            $bookingId = $this->bookingModel->create($bookingData);

            if ($bookingId) {
                $this->setFlash('success', 'Đặt phòng thành công');
                return $this->redirect('/user/profile');
            }

            $this->setFlash('error', reset($this->bookingModel->getErrors()));
        }

        return $this->view('booking/create', [
            'room' => $this->roomModel,
            'hotel' => $hotel,
            'roomType' => $roomType,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'total_price' => $totalPrice
        ]);
    }
}
