<?php

/**
 * File cấu hình chính:
 * - Cấu hình database
 * - URL và paths
 * - Debug mode
 * - Session settings
 * - Timeouts
 * - Constants toàn cục
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'tientt010');
define('DB_PASS', 'tldtt010');
define('DB_NAME', 'agoda');

// Site Configuration
define('SITE_URL', 'http://localhost/agoda');
define('DEBUG_MODE', true);

// Session Configuration
define('SESSION_LIFETIME', 3600);

// Tăng thời gian thực thi PHP
ini_set('max_execution_time', 60);
ini_set('default_socket_timeout', 60);
