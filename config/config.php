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

// Tránh load lại nếu đã được load
if (defined('CONFIG_LOADED')) return;
define('CONFIG_LOADED', true);

// Auto detect environment
define('IS_LOCAL', !isset($_SERVER['USER']) || $_SERVER['USER'] !== 'if0_38486724');

// Local settings
define('DB_HOST', 'localhost');
define('DB_USER', 'tientt010');
define('DB_PASS', 'tldtt010');
define('DB_NAME', 'agoda');

// Site Configuration with dynamic base path
if (!defined('SITE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Get base path from script name
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

    // Define site URL
    define('SITE_URL', rtrim($protocol . $host . $basePath, '/'));
    error_log("SITE_URL defined as: " . SITE_URL);
}

// Debug mode
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);

// Session Configuration
define('SESSION_LIFETIME', 3600);

// Tăng thời gian thực thi PHP
ini_set('max_execution_time', 60);
ini_set('default_socket_timeout', 60);

// Cấu hình phiên làm việc
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
