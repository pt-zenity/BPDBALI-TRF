<?php
/**
 * POST /api/init-db
 * Inisialisasi / reset database schema
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method harus POST', '01', 405);
}

try {
    $msgs = DB::initSchema();
    json_ok(array(
        'message' => 'Database berhasil diinisialisasi',
        'driver'  => DB::driver(),
        'path'    => DB_CONNECTION === 'sqlite' ? SQLITE_PATH : DB_HOST . '/' . DB_DATABASE,
        'detail'  => $msgs,
    ));
} catch (Exception $e) {
    json_err('Gagal init DB: ' . $e->getMessage(), '99', 500);
}
