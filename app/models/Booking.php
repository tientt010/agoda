<?php

namespace App\Models;

use Core\BaseModel;

/**
 * Model Booking - Xử lý các tác vụ đặt phòng:
 * - Tạo đặt phòng mới
 * - Quản lý trạng thái đặt phòng
 * - Xử lý thanh toán
 * - Hủy đặt phòng
 * - Tìm kiếm và lọc đặt phòng
 * - Kiểm tra xung đột booking
 */
class Booking extends BaseModel
{
    // Định nghĩa các const cho status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    // Định nghĩa các const cho payment status
    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUNDED = 'refunded';

    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'total_price',
        'status',
        'payment_status',
        'special_requests'
    ];

    protected $rules = [
        'user_id' => 'required|numeric',
        'room_id' => 'required|numeric',
        'check_in_date' => 'required|date',
        'check_out_date' => 'required|date',
        'total_price' => 'required|numeric|min:0',
        'status' => 'required|in:pending,confirmed,cancelled,completed',
        'payment_status' => 'required|in:unpaid,paid,refunded'
    ];

    /**
     * Tạo booking mới
     * @param array $data
     * @return int|false
     */
    public function create($data)
    {
        // Validate dữ liệu
        if (!$this->validate($data, $this->rules)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Kiểm tra và khóa phòng trước
            $sql = "SELECT * FROM rooms WHERE id = ? FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['room_id']]);
            $room = $stmt->fetch();

            if (!$room) {
                throw new \Exception("Phòng không tồn tại");
            }

            if ($room['status'] !== Room::STATUS_AVAILABLE) {
                throw new \Exception("Phòng không khả dụng");
            }

            // Kiểm tra xung đột booking
            $sql = "SELECT COUNT(*) FROM bookings 
                   WHERE room_id = ? 
                   AND status NOT IN (?, ?)
                   AND ((check_in_date <= ? AND check_out_date >= ?)
                   OR (check_in_date <= ? AND check_out_date >= ?))";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['room_id'],
                self::STATUS_CANCELLED,
                self::STATUS_COMPLETED,
                $data['check_out_date'],
                $data['check_in_date'],
                $data['check_in_date'],
                $data['check_out_date']
            ]);

            if ($stmt->fetchColumn() > 0) {
                throw new \Exception("Phòng đã được đặt trong khoảng thời gian này");
            }

            // Tạo booking
            $bookingId = parent::create($data);

            // Cập nhật trạng thái phòng
            $sql = "UPDATE rooms SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([Room::STATUS_BOOKED, $data['room_id']]);

            $this->db->commit();
            return $bookingId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Cập nhật trạng thái booking
     * @param int $id
     * @param string $status
     * @param string $paymentStatus
     * @return bool
     */
    public function updateStatus($id, $status, $paymentStatus = null)
    {
        $booking = $this->find($id);
        if (!$booking) {
            $this->errors[] = "Booking không tồn tại";
            return false;
        }

        $updateData = ['status' => $status];
        if ($paymentStatus) {
            $updateData['payment_status'] = $paymentStatus;
        }

        try {
            $this->db->beginTransaction();

            // Cập nhật trạng thái booking
            $result = $this->update($id, $updateData);

            // Cập nhật trạng thái phòng nếu cần
            $room = new Room();
            $room->load($booking['room_id']);

            if ($status == self::STATUS_CANCELLED || $status == self::STATUS_COMPLETED) {
                $room->updateStatus(Room::STATUS_AVAILABLE);
            }

            $this->db->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->errors[] = "Lỗi cập nhật: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Lấy chi tiết booking
     * @param int $id
     * @return array|false
     */
    public function getDetails($id)
    {
        $sql = "SELECT b.*, 
                       u.full_name as guest_name,
                       u.email as guest_email,
                       u.phone as guest_phone,
                       r.room_number,
                       r.price_per_night,
                       h.name as hotel_name,
                       rt.name as room_type
                FROM bookings b
                JOIN users u ON b.user_id = u.id 
                JOIN rooms r ON b.room_id = r.id
                JOIN hotels h ON r.hotel_id = h.id
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE b.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Tìm kiếm bookings
     * @param array $filters
     * @return array
     */
    public function search($filters = [])
    {
        $sql = "SELECT b.*, 
                       u.full_name as guest_name,
                       r.room_number,
                       h.name as hotel_name
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                JOIN hotels h ON r.hotel_id = h.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND b.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND b.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND b.check_in_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND b.check_out_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Hủy booking
     * @param int $id
     * @return bool
     */
    public function cancel($id)
    {
        $booking = $this->find($id);
        if (!$booking) {
            $this->errors[] = "Booking không tồn tại";
            return false;
        }

        // Kiểm tra có thể hủy không
        if ($booking['status'] != self::STATUS_PENDING && $booking['status'] != self::STATUS_CONFIRMED) {
            $this->errors[] = "Không thể hủy booking này";
            return false;
        }

        return $this->updateStatus($id, self::STATUS_CANCELLED, self::PAYMENT_REFUNDED);
    }

    /**
     * Xác nhận thanh toán
     * @param int $id
     * @return bool
     */
    public function confirmPayment($id)
    {
        $booking = $this->find($id);
        if (!$booking) {
            $this->errors[] = "Booking không tồn tại";
            return false;
        }

        if ($booking['payment_status'] != self::PAYMENT_UNPAID) {
            $this->errors[] = "Trạng thái thanh toán không hợp lệ";
            return false;
        }

        return $this->updateStatus($id, self::STATUS_CONFIRMED, self::PAYMENT_PAID);
    }
}
