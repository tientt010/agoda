<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Booking;

class AdminController extends BaseController
{
    private $hotelModel;
    private $userModel;
    private $bookingModel;

    public function __construct()
    {
        parent::__construct();
        $this->hotelModel = new Hotel();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    /**
     * Dashboard trang chủ admin
     */
    public function dashboard()
    {
        // Tổng số người dùng
        $totalUsers = count($this->userModel->all());

        // Tổng số khách sạn
        $totalHotels = count($this->hotelModel->all());

        // Tổng số đặt phòng và doanh thu
        $bookings = $this->bookingModel->search();
        $totalBookings = count($bookings);
        $totalRevenue = array_sum(array_column($bookings, 'total_price'));

        return $this->view('admin/dashboard', [
            'totalUsers' => $totalUsers,
            'totalHotels' => $totalHotels,
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue
        ]);
    }

    /**
     * Quản lý khách sạn
     */
    public function hotels()
    {
        $hotels = $this->hotelModel->all();
        return $this->view('admin/hotels', [
            'hotels' => $hotels
        ]);
    }

    /**
     * Quản lý người dùng
     */
    public function users()
    {
        $users = $this->userModel->all();
        return $this->view('admin/users', [
            'users' => $users
        ]);
    }

    /**
     * Quản lý đặt phòng
     */
    public function bookings()
    {
        $bookings = $this->bookingModel->search();
        return $this->view('admin/bookings', [
            'bookings' => $bookings
        ]);
    }
}
