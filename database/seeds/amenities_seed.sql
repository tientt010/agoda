INSERT INTO amenities (name, description) VALUES
('WiFi', 'Internet không dây miễn phí'),
('Swimming Pool', 'Hồ bơi ngoài trời'),
('Gym', 'Phòng tập thể dục'),
('Restaurant', 'Nhà hàng phục vụ 24/7'),
('Spa', 'Dịch vụ massage và spa'),
('Room Service', 'Dịch vụ phòng 24/7'),
('Meeting Room', 'Phòng họp doanh nhân'),
('Free Parking', 'Bãi đỗ xe miễn phí'),
('Bar', 'Quầy bar cao cấp'),
('Airport Shuttle', 'Dịch vụ đưa đón sân bay');

-- Thêm tiện ích cho khách sạn
INSERT INTO hotel_amenities (hotel_id, amenity_id)
SELECT h.id, a.id
FROM hotels h
CROSS JOIN amenities a
WHERE h.star_rating >= 4
LIMIT 50;
