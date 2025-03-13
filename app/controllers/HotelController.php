<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Hotel;
use App\Models\Room;

class HotelController extends BaseController
{
    private $hotelModel;
    private $roomModel;

    public function __construct()
    {
        parent::__construct();
        $this->hotelModel = new Hotel();
        $this->roomModel = new Room();
    }

    /**
     * Hiển thị danh sách khách sạn
     */
    public function index()
    {
        // Lấy các tham số tìm kiếm
        $filters = [
            'city' => $this->getQuery('city'),
            'check_in' => $this->getQuery('check_in'),
            'check_out' => $this->getQuery('check_out'),
            'guests' => $this->getQuery('guests'),
            'star_rating' => $this->getQuery('star_rating'),
            'price_min' => $this->getQuery('price_min'),
            'price_max' => $this->getQuery('price_max'),
        ];

        // Sắp xếp
        $sort = [
            'field' => $this->getQuery('sort_by', 'star_rating'),
            'direction' => $this->getQuery('sort_dir', 'DESC')
        ];

        // Tìm khách sạn
        $hotels = $this->hotelModel->search($filters, $sort);

        return $this->view('hotel/index', [
            'hotels' => $hotels,
            'filters' => $filters,
            'sort' => $sort
        ]);
    }

    /**
     * Hiển thị chi tiết khách sạn
     */
    public function show($id)
    {
        // Lấy thông tin chi tiết khách sạn
        $hotel = $this->hotelModel->getDetails($id);

        if (!$hotel) {
            $this->setFlash('error', 'Không tìm thấy khách sạn');
            return $this->redirect('/');
        }

        // Lấy danh sách phòng trống
        $checkIn = $this->getQuery('check_in', date('Y-m-d', strtotime('+1 day')));
        $checkOut = $this->getQuery('check_out', date('Y-m-d', strtotime('+2 days')));

        $availableRooms = $this->hotelModel->getAvailableRooms($id, $checkIn, $checkOut);

        return $this->view('hotel/show', [
            'hotel' => $hotel,
            'rooms' => $availableRooms,
            'check_in' => $checkIn,
            'check_out' => $checkOut
        ]);
    }
}
