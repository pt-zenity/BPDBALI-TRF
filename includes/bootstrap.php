<?php
/**
 * Bootstrap - Load semua dependencies
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_error.log');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Init CORS
cors();

// Init DB connection (auto-connect saat pertama dipakai)
try {
    DB::connect();
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(array(
        'status'  => '99',
        'message' => 'Koneksi database gagal: ' . $e->getMessage(),
        'driver'  => DB_CONNECTION,
        'host'    => DB_HOST,
    ));
    exit;
}
