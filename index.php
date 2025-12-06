<?php
/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

// Disable error display in production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Define application paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('RESOURCES_PATH', ROOT_PATH . '/resources');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Check if vendor directory exists
if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    die('Please run "composer install" first.');
}

// Load composer autoload
require ROOT_PATH . '/vendor/autoload.php';

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

// Set base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host);

// Bootstrap application
$app = require ROOT_PATH . '/bootstrap/app.php';

// Run application
$app->run();

// Flush output buffer
ob_end_flush();