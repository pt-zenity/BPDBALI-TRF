<?php
/**
 * GET /api/bank-list  - Daftar bank
 */
require_once __DIR__ . '/../includes/bootstrap.php';

try {
    $banks = DB::all("SELECT bank_code, bank_name, transfer_cost FROM gcore_bankcode ORDER BY bank_name");
    json_ok(['data' => $banks]);
} catch (Exception $e) {
    json_err($e->getMessage(), '99', 500);
}
