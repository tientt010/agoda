<?php

namespace App\Models;

use Core\BaseModel;

/**
 * Model Hotel - Xử lý các tác vụ liên quan đến khách sạn:
 * - Tìm kiếm khách sạn theo nhiều tiêu chí
 * - Quản lý thông tin chi tiết khách sạn
 * - Quản lý phòng và tình trạng phòng
 * - Quản lý tiện ích khách sạn
 * - Thống kê booking và doanh thu
 */
class Hotel extends BaseModel
{
    // Định nghĩa tên bảng
    protected $table = 'hotels';

    // Các trường được phép gán giá trị hàng loạt
    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'description',
        'star_rating',
        'latitude',
        'longitude',
        'check_in_time',
        'check_out_time',
        'contact_phone',
        'contact_email',
        'status'
    ];

    // Định nghĩa rules cho validation
    protected $rules = [
        'name' => 'required|max:100',
        'address' => 'required',
        'city' => 'required|max:50',
        'country' => 'required|max:50',
        'star_rating' => 'required|numeric|min:1|max:5',
        'contact_email' => 'email',
        'contact_phone' => 'max:20'
    ];

    /**
     * Tìm khách sạn theo điều kiện tìm kiếm
     * @param array $filters Các điều kiện lọc
     * @param array $sort Thông tin sắp xếp
     * @return array Danh sách khách sạn
     */
    public function search($filters = [], $sort = ['field' => 'star_rating', 'direction' => 'DESC'])
    {
        try {
            // Chuẩn bị câu query cơ bản
            $sql = "SELECT DISTINCT h.*, 
                    MIN(r.price_per_night) as min_price,
                    COUNT(DISTINCT r.id) as total_rooms,
                    COUNT(DISTINCT ha.amenity_id) as total_amenities
                   FROM hotels h
                   LEFT JOIN rooms r ON h.id = r.hotel_id
                   LEFT JOIN hotel_amenities ha ON h.id = ha.hotel_id
                   WHERE h.status = 'active'";
            $params = [];

            // Thêm điều kiện tìm kiếm
            if (!empty($filters['city'])) {
                $sql .= " AND h.city LIKE ?";
                $params[] = '%' . $filters['city'] . '%';
            }

            if (!empty($filters['star_rating'])) {
                $sql .= " AND h.star_rating = ?";
                $params[] = $filters['star_rating'];
            }

            if (!empty($filters['price_min'])) {
                $sql .= " AND r.price_per_night >= ?";
                $params[] = $filters['price_min'];
            }

            if (!empty($filters['price_max'])) {
                $sql .= " AND r.price_per_night <= ?";
                $params[] = $filters['price_max'];
            }

            // Check-in/out dates để kiểm tra phòng trống
            if (!empty($filters['check_in']) && !empty($filters['check_out'])) {
                $sql .= " AND h.id NOT IN (
                    SELECT DISTINCT r2.hotel_id 
                    FROM rooms r2 
                    JOIN bookings b ON r2.id = b.room_id 
                    WHERE b.status != 'cancelled'
                    AND ((b.check_in_date <= ? AND b.check_out_date >= ?)
                    OR (b.check_in_date <= ? AND b.check_out_date >= ?))
                )";
                $params[] = $filters['check_out'];
                $params[] = $filters['check_in'];
                $params[] = $filters['check_in'];
                $params[] = $filters['check_out'];
            }

            // Group by để tránh duplicate
            $sql .= " GROUP BY h.id";

            // Validate và set default cho sort params 
            $validSortFields = ['star_rating', 'min_price', 'total_rooms'];
            $sortField = isset($sort['field']) && in_array($sort['field'], $validSortFields)
                ? $sort['field']
                : 'star_rating';

            $sortDirection = isset($sort['direction']) && strtoupper($sort['direction']) === 'ASC'
                ? 'ASC'
                : 'DESC';

            $sql .= " ORDER BY {$sortField} {$sortDirection}";

            // Thực thi query
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll();

            // Thêm thông tin tiện ích cho mỗi khách sạn
            foreach ($results as &$hotel) {
                $hotel['amenities'] = $this->getHotelAmenities($hotel['id']);
                $hotel['available_rooms'] = $this->getAvailableRoomTypes($hotel['id']);
            }

            return $results;
        } catch (\PDOException $e) {
            $this->errors[] = "Lỗi tìm kiếm: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Lấy danh sách tiện ích của khách sạn
     * @param int $hotelId
     * @return array
     */
    private function getHotelAmenities($hotelId)
    {
        $sql = "SELECT a.* 
                FROM amenities a
                JOIN hotel_amenities ha ON a.id = ha.amenity_id
                WHERE ha.hotel_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin các loại phòng có sẵn
     * @param int $hotelId
     * @return array
     */
    private function getAvailableRoomTypes($hotelId)
    {
        $sql = "SELECT rt.*, COUNT(r.id) as room_count, MIN(r.price_per_night) as min_price
                FROM room_types rt
                JOIN rooms r ON rt.id = r.room_type_id
                WHERE r.hotel_id = ? AND r.status = 'available'
                GROUP BY rt.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin chi tiết khách sạn bao gồm các phòng có sẵn
     * @param int $id ID của khách sạn
     * @return array|false Thông tin chi tiết khách sạn hoặc false nếu không tìm thấy
     */
    public function getDetails($id)
    {
        $hotel = $this->find($id);
        if (!$hotel) return false;

        // Lấy danh sách phòng
        $sql = "SELECT r.*, rt.name as room_type, rt.description as room_description
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.hotel_id = ? AND r.status = 'available'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $hotel['rooms'] = $stmt->fetchAll();

        // Lấy tiện ích
        $sql = "SELECT a.*
                FROM amenities a
                JOIN hotel_amenities ha ON a.id = ha.amenity_id
                WHERE ha.hotel_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $hotel['amenities'] = $stmt->fetchAll();

        return $hotel;
    }

    /**
     * Kiểm tra phòng trống theo ngày
     * @param int $hotelId ID khách sạn
     * @param string $checkIn Ngày check-in
     * @param string $checkOut Ngày check-out
     * @return array Danh sách phòng trống
     */
    public function getAvailableRooms($hotelId, $checkIn, $checkOut)
    {
        $sql = "SELECT r.*, rt.name as room_type, rt.description
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.hotel_id = ?
                AND r.status = 'available'
                AND r.id NOT IN (
                    SELECT room_id FROM bookings
                    WHERE (check_in_date <= ? AND check_out_date >= ?)
                    OR (check_in_date <= ? AND check_out_date >= ?)
                    AND status != 'cancelled'
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hotelId, $checkOut, $checkIn, $checkIn, $checkOut]);
        return $stmt->fetchAll();
    }

    /**
     * Thêm tiện ích cho khách sạn
     * @param int $hotelId ID khách sạn
     * @param array $amenityIds Mảng ID các tiện ích
     * @return bool Kết quả thêm tiện ích
     */
    public function addAmenities($hotelId, $amenityIds)
    {
        try {
            $sql = "INSERT INTO hotel_amenities (hotel_id, amenity_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);

            foreach ($amenityIds as $amenityId) {
                $stmt->execute([$hotelId, $amenityId]);
            }
            return true;
        } catch (\PDOException $e) {
            $this->errors[] = "Lỗi khi thêm tiện ích: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Lấy thống kê đặt phòng của khách sạn
     * @param int $hotelId ID khách sạn
     * @param string $startDate Ngày bắt đầu
     * @param string $endDate Ngày kết thúc
     * @return array Thống kê đặt phòng
     */
    public function getBookingStats($hotelId, $startDate, $endDate)
    {
        try {
            // Query để lấy thống kê từ các booking thuộc về các phòng của hotel
            $sql = "SELECT 
                    h.id,
                    COUNT(DISTINCT b.id) as total_bookings,
                    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN b.status = 'confirmed' AND b.payment_status = 'paid' THEN b.total_price ELSE 0 END) as total_revenue,
                    (SELECT COUNT(*) FROM rooms WHERE hotel_id = ?) as total_rooms,
                    AVG(CASE 
                        WHEN b.status = 'confirmed' 
                        THEN DATEDIFF(b.check_out_date, b.check_in_date)
                        ELSE NULL 
                    END) as avg_stay_duration
                    FROM hotels h
                    INNER JOIN rooms r ON h.id = r.hotel_id
                    LEFT JOIN bookings b ON r.id = b.room_id 
                    WHERE h.id = ?
                    AND (
                        b.check_in_date IS NULL OR 
                        (b.check_in_date BETWEEN ? AND ? OR b.check_out_date BETWEEN ? AND ?)
                    )
                    GROUP BY h.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$hotelId, $hotelId, $startDate, $endDate, $startDate, $endDate]);
            $stats = $stmt->fetch();

            // Nếu không có booking nào, lấy ít nhất thông tin số phòng của hotel
            if (!$stats) {
                $totalRooms = $this->getTotalRooms($hotelId);
                return [
                    'total_bookings' => 0,
                    'confirmed_bookings' => 0,
                    'cancelled_bookings' => 0,
                    'total_revenue' => 0,
                    'total_rooms' => $totalRooms,
                    'avg_stay_duration' => 0,
                    'booking_rate' => 0
                ];
            }

            // Xử lý và làm tròn số liệu
            $stats['total_bookings'] = (int)$stats['total_bookings'];
            $stats['confirmed_bookings'] = (int)$stats['confirmed_bookings'];
            $stats['cancelled_bookings'] = (int)$stats['cancelled_bookings'];
            $stats['total_revenue'] = (float)$stats['total_revenue'];
            $stats['total_rooms'] = (int)$stats['total_rooms'];
            $stats['avg_stay_duration'] = round($stats['avg_stay_duration'] ?: 0, 1);
            $stats['booking_rate'] = $stats['total_bookings'] > 0
                ? round(($stats['confirmed_bookings'] / $stats['total_bookings']) * 100, 2)
                : 0;

            return $stats;
        } catch (\PDOException $e) {
            $this->errors[] = "Lỗi khi lấy thống kê: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Lấy tổng số phòng của khách sạn
     * @param int $hotelId
     * @return int
     */
    private function getTotalRooms($hotelId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ?");
        $stmt->execute([$hotelId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách khách sạn nổi bật
     * @return array
     */
    public function getFeaturedHotels($limit = 6)
    {
        try {
            $sql = "SELECT h.*, 
                    MIN(r.price_per_night) as min_price,
                    COUNT(DISTINCT r.id) as total_rooms,
                    COALESCE(AVG(rb.rating), 0) as avg_rating,
                    COUNT(DISTINCT rb.id) as review_count
                   FROM hotels h
                   LEFT JOIN rooms r ON h.id = r.hotel_id
                   LEFT JOIN room_bookings rb ON h.id = rb.hotel_id
                   WHERE h.status = 'active'
                   GROUP BY h.id
                   HAVING total_rooms > 0
                   ORDER BY avg_rating DESC, review_count DESC
                   LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            $hotels = $stmt->fetchAll();

            // Thêm đường dẫn ảnh cho mỗi khách sạn
            foreach ($hotels as &$hotel) {
                $hotel['image'] = $this->getHotelImage($hotel['id']);
            }

            return $hotels;
        } catch (\PDOException $e) {
            $this->errors[] = "Lỗi khi lấy danh sách khách sạn nổi bật: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Lấy danh sách thành phố phổ biến
     * @return array
     */
    public function getPopularCities($limit = 6)
    {
        try {
            $sql = "SELECT h.city,
                    COUNT(DISTINCT h.id) as hotel_count,
                    COUNT(DISTINCT b.id) as booking_count
                   FROM hotels h
                   LEFT JOIN rooms r ON h.id = r.hotel_id
                   LEFT JOIN bookings b ON r.id = b.room_id
                   GROUP BY h.city 
                   ORDER BY booking_count DESC
                   LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            $cities = $stmt->fetchAll();

            // Thêm thông tin và hình ảnh cho mỗi thành phố
            foreach ($cities as &$city) {
                $city['name'] = $city['city'];
                $city['image'] = $this->getCityImage($city['city']);
            }

            return $cities;
        } catch (\PDOException $e) {
            $this->errors[] = "Lỗi khi lấy danh sách thành phố phổ biến: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Lấy ảnh đại diện của khách sạn
     * @param int $hotelId
     * @return string
     */
    private function getHotelImage($hotelId)
    {
        // Using PUBLIC_PATH constant
        return SITE_URL . '/public/images/hotels/default.jpg';
    }

    /**
     * Lấy ảnh đại diện của thành phố
     * @param string $cityName
     * @return string
     */
    private function getCityImage($cityName)
    {
        // Using PUBLIC_PATH constant  
        return SITE_URL . '/public/images/cities/default.jpg';
    }
}
