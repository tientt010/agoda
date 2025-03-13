CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    avatar VARCHAR(255) DEFAULT 'default.jpg',
    birth_date DATE,
    gender ENUM('male', 'female', 'other'),
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
