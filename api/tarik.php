<?php
/**
 * POST /api/tarik  - Penarikan Tunai
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b     = get_body();
$norek = trim($b['norek']  ?? '');
$jumlah = (float)($b['amount'] ?? $b['jumlah'] ?? 0);
$ket   = trim($b['remark'] ?? $b['keterangan'] ?? 'Tarik tunai');
$user  = trim($b['userid'] ?? 'teller');

if (!$norek)      json_err('No rekening wajib', '01');
if ($jumlah <= 0) json_err('Jumlah penarikan harus lebih dari 0', '01');
if ($jumlah < 10000) json_err('Jumlah penarikan minimal Rp 10.000', '01');

try {
    $rek = DB::first(
        "SELECT r.*, n.status as status_nasabah
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$norek]
    );
    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);
    if ($rek['status'] !== 'A') json_err('Rekening tidak aktif', '05');
    if ($rek['status_nasabah'] !== 'A') json_err('Status nasabah tidak aktif', '05');

    $saldo = (float)$rek['saldo'];

    // Cek saldo minimum
    if (($saldo - $jumlah) < SALDO_MIN) {
        json_err(
            'Saldo tidak cukup. Saldo tersedia: ' . rp($saldo) .
            '. Minimum saldo mengendap: ' . rp(SALDO_MIN),
            '51'
        );
    }

    DB::begin();
    $transNo   = insert_folio($norek, $jumlah, 0, $ket ?: 'Tarik tunai', 'TT', $user);
    $saldoBaru = get_saldo($norek);
    DB::commit();

    log_trans($norek, 'TARIK', $ket, $jumlah);

    json_ok([
        'message'    => 'Penarikan tunai berhasil',
        'trans_no'   => $transNo,
        'norek'      => $norek,
        'nama'       => $rek['nama'],
        'jumlah'     => $jumlah,
        'jumlah_fmt' => rp($jumlah),
        'saldo'      => $saldoBaru,
        'saldo_fmt'  => rp($saldoBaru),
    ]);
} catch (Exception $e) {
    DB::rollback();
    error_log('[tarik] ' . $e->getMessage());
    json_err('Gagal tarik: ' . $e->getMessage(), '99', 500);
}
