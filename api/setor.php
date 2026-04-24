<?php
/**
 * POST /api/setor  - Setoran Tunai
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b     = get_body();
$norek = trim($b['norek']   ?? '');
$jumlah = (float)($b['amount'] ?? $b['jumlah'] ?? 0);
$ket   = trim($b['remark']  ?? $b['keterangan'] ?? 'Setor tunai');
$user  = trim($b['userid']  ?? 'teller');

if (!$norek)   json_err('No rekening wajib', '01');
if ($jumlah <= 0) json_err('Jumlah setoran harus lebih dari 0', '01');
if ($jumlah < 1000) json_err('Jumlah setoran minimal Rp 1.000', '01');

try {
    // Cek rekening
    $rek = DB::first(
        "SELECT r.*, n.status as status_nasabah, n.nama as nama_nas
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$norek]
    );
    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);
    if ($rek['status'] !== 'A') json_err('Rekening tidak aktif', '05');
    if ($rek['status_nasabah'] !== 'A') json_err('Status nasabah tidak aktif', '05');

    DB::begin();
    $transNo  = insert_folio($norek, 0, $jumlah, $ket ?: 'Setor tunai', 'ST', $user);
    $saldoBaru = get_saldo($norek);
    DB::commit();

    log_trans($norek, 'SETOR', $ket, $jumlah);

    json_ok([
        'message'   => 'Setor tunai berhasil',
        'trans_no'  => $transNo,
        'norek'     => $norek,
        'nama'      => $rek['nama'],
        'jumlah'    => $jumlah,
        'jumlah_fmt' => rp($jumlah),
        'saldo'     => $saldoBaru,
        'saldo_fmt' => rp($saldoBaru),
    ]);
} catch (Exception $e) {
    DB::rollback();
    error_log('[setor] ' . $e->getMessage());
    json_err('Gagal setor: ' . $e->getMessage(), '99', 500);
}
