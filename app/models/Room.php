<?php

namespace App\Models;

use Core\BaseModel;

/**
 * Model Room - Xử lý các tác vụ liên quan đến phòng:
 * - CRUD operations cho phòng
 * - Kiểm tra tình trạng phòng trống 
 * - Tìm kiếm phòng theo loại và thời gian
 * - Tính giá phòng
 * - Quản lý trạng thái phòng
 * - Thống kê phòng theo khách sạn
 */
class Room extends BaseModel
{
    // Định nghĩa các const cho status
    const STATUS_AVAILABLE = 'available';
    const STATUS_BOOKED = 'booked';
    const STATUS_MAINTENANCE = 'maintenance';

    // Định nghĩa const cho booking status 
    const BOOKING_PENDING = 'pending';
    const BOOKING_CONFIRMED = 'confirmed';
    const BOOKING_CANCELLED = 'cancelled';
    const BOOKING_COMPLETED = 'completed';

    protected $table = 'rooms';

    // Cập nhật fillable theo đúng schema
    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'room_number',
        'price_per_night',
        'status',
        'created_at'
    ];

    // Cập nhật rules phù hợp với schema
    protected $rules = [
        'hotel_id' => 'required|numeric',
        'room_type_id' => 'required|numeric',
        'room_number' => 'required|max:20',
        'price_per_night' => 'required|numeric|min:0',
        'status' => 'required|in:available,booked,maintenance'
    ];

    protected $data = [];

    public function __construct()
    {
        parent::__construct();
        // Khởi tạo các thuộc tính cần thiết
        $this->data = [
            'id' => null,
            'hotel_id' => null,
            'room_type_id' => null,
            'room_number' => null,
            'price_per_night' => 0,
            'status' => self::STATUS_AVAILABLE
        ];
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function load($id)
    {
        $data = $this->find($id);
        if ($data) {
            $this->data = $data;
            return true;
        }
        return false;
    }

    /**
     * Tạo phòng mới với validation
     * @param array $data
     * @return int|false
     */
    public function create($data)
    {
        // Validate data theo rules
        if (!$this->validate($data, $this->rules)) {
            return false;
        }

        // Kiểm tra hotel tồn tại
        $hotelExists = $this->db->prepare("SELECT id FROM hotels WHERE id = ?");
        $hotelExists->execute([$data['hotel_id']]);
        if (!$hotelExists->fetch()) {
            $this->errors[] = "Hotel không tồn tại";
            return false;
        }

        // Kiểm tra room type tồn tại
        $roomTypeExists = $this->db->prepare("SELECT id FROM room_types WHERE id = ?");
        $roomTypeExists->execute([$data['room_type_id']]);
        if (!$roomTypeExists->fetch()) {
            $this->errors[] = "Loại phòng không tồn tại";
            return false;
        }

        try {
            $this->db->beginTransaction();
            $id = parent::create($data);
            $this->db->commit();
            return $id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->errors[] = "Lỗi tạo phòng: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Update với validation và transaction
     */
    public function update($id, $data)
    {
        // Validate hotel_id và room_type_id nếu có
        if (isset($data['hotel_id'])) {
            $hotelExists = $this->db->prepare("SELECT id FROM hotels WHERE id = ?");
            $hotelExists->execute([$data['hotel_id']]);
            if (!$hotelExists->fetch()) {
                $this->errors[] = "Hotel không tồn tại";
                return false;
            }
        }

        if (isset($data['room_type_id'])) {
            $roomTypeExists = $this->db->prepare("SELECT id FROM room_types WHERE id = ?");
            $roomTypeExists->execute([$data['room_type_id']]);
            if (!$roomTypeExists->fetch()) {
                $this->errors[] = "Loại phòng không tồn tại";
                return false;
            }
        }

        try {
            $this->db->beginTransaction();
            $result = parent::update($id, $data);
            $this->db->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->errors[] = "Lỗi cập nhật: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Kiểm tra phòng có available trong khoảng thời gian
     * @param string $checkInDate Format: Y-m-d
     * @param string $checkOutDate Format: Y-m-d
     * @return bool
     */
    public function isAvailable($checkInDate, $checkOutDate)
    {
        // Validate dates
        if (!$this->validateDates($checkInDate, $checkOutDate)) {
            return false;
        }

        // Load current room data if not loaded
        if (!$this->id) {
            $this->errors[] = "Chưa load thông tin phòng";
            return false;
        }

        // Check current status
        if ($this->status !== self::STATUS_AVAILABLE) {
            $this->errors[] = "Phòng không khả dụng";
            return false;
        }

        return !$this->hasBookingConflict($this->id, $checkInDate, $checkOutDate);
    }

    /**
     * Tìm phòng trống theo loại phòng và thời gian
     * @param int $roomTypeId
     * @param string $checkInDate Format: Y-m-d 
     * @param string $checkOutDate Format: Y-m-d
     * @return array
     */
    public function findAvailableByType($roomTypeId, $checkInDate, $checkOutDate)
    {
        if (!$this->validateDates($checkInDate, $checkOutDate)) {
            return [];
        }

        $sql = "SELECT r.*, rt.name as room_type_name, rt.base_capacity, rt.max_capacity
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.room_type_id = ?
                AND r.status = ?
                AND r.id NOT IN (
                    SELECT room_id FROM bookings 
                    WHERE status NOT IN ('cancelled', 'completed')
                    AND (
                        (check_in_date < ? AND check_out_date > ?)
                        OR (check_in_date < ? AND check_out_date > ?)
                        OR (? <= check_in_date AND check_out_date <= ?)
                    )
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $roomTypeId,
            self::STATUS_AVAILABLE,
            $checkOutDate,
            $checkInDate,
            $checkInDate,
            $checkInDate,
            $checkInDate,
            $checkOutDate
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Thống kê tình trạng phòng
     * @param int $hotelId
     * @return array
     */
    public function getRoomStats($hotelId)
    {
        $sql = "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as available_rooms,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as booked_rooms,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as maintenance_rooms,
                MIN(price_per_night) as min_price,
                MAX(price_per_night) as max_price,
                AVG(price_per_night) as avg_price
                FROM rooms
                WHERE hotel_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            self::STATUS_AVAILABLE,
            self::STATUS_BOOKED,
            self::STATUS_MAINTENANCE,
            $hotelId
        ]);

        return $stmt->fetch();
    }

    /**
     * Validate ngày check-in/out
     * @param string $checkInDate Ngày check-in
     * @param string $checkOutDate Ngày check-out
     * @return bool
     */
    private function validateDates($checkInDate, $checkOutDate)
    {
        $today = date('Y-m-d');
        $checkIn = date('Y-m-d', strtotime($checkInDate));
        $checkOut = date('Y-m-d', strtotime($checkOutDate));

        if ($checkIn < $today) {
            $this->errors[] = "Ngày check-in không thể trong quá khứ";
            return false;
        }

        if ($checkOut <= $checkIn) {
            $this->errors[] = "Ngày check-out phải sau ngày check-in";
            return false;
        }

        return true;
    }

    /**
     * Tính tổng giá phòng cho khoảng thời gian
     * @param string $checkInDate Ngày check-in
     * @param string $checkOutDate Ngày check-out
     * @return float
     */
    public function calculateTotalPrice($checkInDate, $checkOutDate)
    {
        if (!$this->validateDates($checkInDate, $checkOutDate)) {
            return false;
        }

        if (!$this->price_per_night) {
            $this->errors[] = "Chưa có thông tin giá phòng";
            return false;
        }

        $nights = (strtotime($checkOutDate) - strtotime($checkInDate)) / (60 * 60 * 24);

        if ($nights < 1) {
            $this->errors[] = "Thời gian đặt phòng không hợp lệ";
            return false;
        }

        return round($this->price_per_night * $nights, 2);
    }

    /**
     * Cập nhật trạng thái phòng
     * @param string $newStatus Trạng thái mới
     * @return bool
     */
    public function updateStatus($newStatus)
    {
        $validStatuses = [
            self::STATUS_AVAILABLE,
            self::STATUS_BOOKED,
            self::STATUS_MAINTENANCE
        ];

        if (!in_array($newStatus, $validStatuses)) {
            $this->errors[] = "Invalid room status: {$newStatus}";
            return false;
        }

        return $this->update($this->id, ['status' => $newStatus]);
    }

    /**
     * Lấy thông tin khách sạn của phòng an toàn hơn
     * @return array|false
     */
    public function getHotelDetails()
    {
        if (!$this->hotel_id) {
            $this->errors[] = "Chưa có thông tin khách sạn";
            return false;
        }

        $sql = "SELECT h.*, COUNT(r.id) as total_rooms
                FROM hotels h
                LEFT JOIN rooms r ON h.id = r.hotel_id
                WHERE h.id = ?
                GROUP BY h.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->hotel_id]);
        return $stmt->fetch();
    }

    /**
     * Lấy thông tin loại phòng an toàn hơn
     * @return array|false
     */
    public function getRoomTypeDetails()
    {
        if (!$this->room_type_id) {
            $this->errors[] = "Chưa có thông tin loại phòng";
            return false;
        }

        $sql = "SELECT rt.*, COUNT(r.id) as rooms_count
                FROM room_types rt
                LEFT JOIN rooms r ON rt.id = r.room_type_id
                WHERE rt.id = ?
                GROUP BY rt.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->room_type_id]);
        return $stmt->fetch();
    }

    /**
     * Lấy lịch sử đặt phòng
     * @param array $filters Các điều kiện lọc
     * @return array
     */
    public function getBookingHistory($filters = [])
    {
        $sql = "SELECT b.*, 
                       u.full_name as guest_name,
                       u.email as guest_email,
                       u.phone as guest_phone,
                       DATEDIFF(b.check_out_date, b.check_in_date) as nights,
                       rt.name as room_type_name
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE b.room_id = ?";

        $params = [$this->id];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND b.check_in_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND b.check_out_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY b.check_in_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Kiểm tra xung đột booking chi tiết hơn
     * @param int $roomId
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    private function hasBookingConflict($roomId, $checkIn, $checkOut)
    {
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE room_id = ?
                AND status NOT IN (?, ?)
                AND (
                    (check_in_date < ? AND check_out_date > ?)
                    OR (check_in_date < ? AND check_out_date > ?)
                    OR (check_in_date >= ? AND check_out_date <= ?)
                    OR (? BETWEEN check_in_date AND check_out_date)
                    OR (? BETWEEN check_in_date AND check_out_date)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $roomId,
            self::BOOKING_CANCELLED,
            self::BOOKING_COMPLETED,
            $checkOut,
            $checkIn,
            $checkIn,
            $checkIn,
            $checkIn,
            $checkOut,
            $checkIn,
            $checkOut
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Kiểm tra phòng có thể bảo trì không
     */
    public function canMaintenance()
    {
        return !$this->hasBookingConflict(
            $this->id,
            date('Y-m-d'),
            date('Y-m-d', strtotime('+30 days'))
        );
    }
}
