<?php
// Base paths
define('BASE_PATH', str_replace('\\', '/', dirname(__DIR__)));
define('ROOT_PATH', BASE_PATH);

// Get project folder from script path
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$projectFolder = trim($scriptDir, '/');

// Define constants
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('VIEW_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('MODEL_PATH', APP_PATH . '/models');
define('MIDDLEWARE_PATH', APP_PATH . '/middleware');

// URL paths
define('SITE_ROOT', '/' . $projectFolder);
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
define('SITE_URL', rtrim($protocol . $host . SITE_ROOT, '/'));

// Debug mode
define('DEBUG_MODE', true);

// Debug logging
if (DEBUG_MODE) {
    error_log('BASE_PATH: ' . BASE_PATH);
    error_log('SITE_ROOT: ' . SITE_ROOT);
    error_log('SITE_URL: ' . SITE_URL);
    error_log('OS: ' . PHP_OS);
    error_log('ROOT_PATH: ' . ROOT_PATH);
    error_log('File structure check:');
    error_log('Controller path exists: ' . (is_dir(CONTROLLER_PATH) ? 'Yes' : 'No'));
    error_log('Model path exists: ' . (is_dir(MODEL_PATH) ? 'Yes' : 'No'));
    error_log('Current URL: ' . $_SERVER['REQUEST_URI']);
    error_log('Clean Site Root: ' . SITE_ROOT);
    error_log('Project Folder: ' . $projectFolder);
    error_log('Script Name: ' . $_SERVER['SCRIPT_NAME']);
    error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
}
