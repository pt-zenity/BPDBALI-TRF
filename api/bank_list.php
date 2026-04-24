<?php
/**
 * GET /api/bank-list  - Daftar bank
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

try {
    $banks = DB::all("SELECT bank_code, bank_name, transfer_cost FROM gcore_bankcode ORDER BY bank_name");
    json_ok(array('data' => $banks));
} catch (Exception $e) {
    error_log('[bank_list] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
