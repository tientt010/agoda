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

// Auto detect environment
define('IS_LOCAL', !isset($_SERVER['USER']) || $_SERVER['USER'] !== 'if0_38486724');

// Auto detect environment and project folder
$isProduction = isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false;

// Use BASE_PATH for file includes
require_once BASE_PATH . '/config/paths.php';

// Check if BASE_PATH is defined
if (!defined('BASE_PATH')) {
    die('BASE_PATH must be defined first');
}


// Local settings
define('DB_HOST', 'localhost');
define('DB_USER', 'tientt010');
define('DB_PASS', 'tldtt010');
define('DB_NAME', 'agoda');


// Site Configuration with dynamic base path
if (!defined('SITE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = $isProduction ? '' : SITE_ROOT;  // Use dynamic SITE_ROOT
    define('SITE_URL', rtrim($protocol . $host . $basePath, '/'));
}

if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);

// Session Configuration
define('SESSION_LIFETIME', 3600);

// Tăng thời gian thực thi PHP
ini_set('max_execution_time', 60);
ini_set('default_socket_timeout', 60);
