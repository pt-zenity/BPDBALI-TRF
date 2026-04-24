<?php
/**
 * GET /api/saldo/{norek}  - Cek saldo
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $uri)));
$norek = isset($parts[2]) ? urldecode($parts[2]) : '';

if (!$norek) json_err('No rekening wajib', '01');

try {
    $rek = DB::first(
        "SELECT r.notab, r.nama, r.saldo, r.status, r.produk, n.noid
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        array($norek)
    );
    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);

    json_ok(array(
        'norek'     => $rek['notab'],
        'nama'      => $rek['nama'],
        'produk'    => $rek['produk'],
        'saldo'     => (float)$rek['saldo'],
        'saldo_fmt' => rp((float)$rek['saldo']),
        'status'    => $rek['status'],
    ));
} catch (Exception $e) {
    json_err($e->getMessage(), '99', 500);
}
