CREATE TABLE amenities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hotel_amenities (
    hotel_id INT,
    amenity_id INT,
    PRIMARY KEY (hotel_id, amenity_id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (amenity_id) REFERENCES amenities(id)
);
