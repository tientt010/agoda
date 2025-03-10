<?php

namespace App\Controllers;

use Core\BaseController;
use App\Models\Hotel;

class HomeController extends BaseController
{
    private $hotelModel;

    public function __construct()
    {
        parent::__construct();
        $this->hotelModel = new Hotel();
    }

    public function index()
    {
        // Lấy các params tìm kiếm từ URL
        $filters = [
            'city' => $this->getQuery('city'),
            'check_in' => $this->getQuery('check_in'),
            'check_out' => $this->getQuery('check_out'),
            'guests' => $this->getQuery('guests'),
            'star_rating' => $this->getQuery('star_rating'),
            'price_min' => $this->getQuery('price_min'),
            'price_max' => $this->getQuery('price_max'),
        ];

        // Lấy thông tin sắp xếp
        $sort = [
            'field' => $this->getQuery('sort_by', 'star_rating'),
            'direction' => $this->getQuery('sort_dir', 'DESC')
        ];

        // Lấy danh sách khách sạn nổi bật
        $featuredHotels = $this->hotelModel->getFeaturedHotels();

        // Lấy danh sách khách sạn theo filter
        $hotels = $this->hotelModel->search($filters, $sort);

        // Lấy danh sách thành phố phổ biến
        $popularCities = $this->hotelModel->getPopularCities();

        // Lấy deals và khuyến mãi
        $promotions = $this->getPromotions();

        return $this->view('home/index', [
            'featuredHotels' => $featuredHotels,
            'hotels' => $hotels,
            'popularCities' => $popularCities,
            'promotions' => $promotions,
            'filters' => $filters,
            'sort' => $sort
        ]);
    }

    private function getPromotions()
    {
        return [
            [
                'title' => 'Giảm 25% cho đặt phòng sớm',
                'description' => 'Đặt trước 30 ngày để được giảm giá đặc biệt',
                'code' => 'EARLY25',
                'discount' => 25,
                'expires' => '2024-12-31'
            ],
            [
                'title' => 'Ưu đãi cuối tuần',
                'description' => 'Giảm 15% cho booking cuối tuần',
                'code' => 'WEEKEND15',
                'discount' => 15,
                'expires' => '2024-12-31'
            ],
            // Thêm các khuyến mãi khác...
        ];
    }
}
