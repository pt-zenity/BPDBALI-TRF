<?php
/**
 * GET /api/rekening/{norek}  - Info rekening + saldo
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $uri)));
$norek = urldecode($parts[2] ?? '');

if (!$norek) json_err('No rekening wajib', '01');

try {
    $rek = DB::first(
        "SELECT r.*, n.nama as nama_nasabah, n.phone, n.status as status_nasabah, n.noid
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$norek]
    );

    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);

    json_ok([
        'data' => [
            'norek'           => $rek['notab'],
            'nama'            => $rek['nama'],
            'produk'          => $rek['produk'],
            'saldo'           => (float)$rek['saldo'],
            'saldo_fmt'       => rp((float)$rek['saldo']),
            'status_rek'      => $rek['status'],
            'status_nasabah'  => $rek['status_nasabah'] ?? '-',
            'phone'           => $rek['phone'] ?? '',
            'noid'            => $rek['noid']  ?? '',
        ]
    ]);
} catch (Exception $e) {
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
