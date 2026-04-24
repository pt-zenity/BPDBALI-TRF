<?php
/**
 * POST /api/transfer-bank  - Transfer ke Bank Lain
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b        = get_body();
$fromRek  = trim($b['from_norek'] ?? $b['from_acc'] ?? '');
$toBankCode = trim($b['bank_code'] ?? '');
$toBankAcc  = trim($b['bank_acc']  ?? $b['to_acc'] ?? '');
$toBankName = trim($b['to_name']   ?? '');
$jumlah   = (float)($b['amount']   ?? $b['jumlah'] ?? 0);
$ket      = trim($b['remark']      ?? 'Transfer ke bank lain');
$refNo    = trim($b['ref_no']      ?? trans_no('REF'));

if (!$fromRek)    json_err('No rekening pengirim wajib', '01');
if (!$toBankCode) json_err('Kode bank tujuan wajib', '01');
if (!$toBankAcc)  json_err('No rekening bank tujuan wajib', '01');
if ($jumlah < MIN_TRANSFER) json_err('Nominal minimal: ' . rp(MIN_TRANSFER), '01');
if ($jumlah > MAX_TRANSFER) json_err('Nominal maksimal: ' . rp(MAX_TRANSFER), '01');

try {
    // Cek rekening pengirim
    $rekFrom = DB::first(
        "SELECT r.*, n.status as status_nas FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$fromRek]
    );
    if (!$rekFrom) json_err('Rekening tidak ditemukan', '04', 404);
    if ($rekFrom['status'] !== 'A') json_err('Rekening tidak aktif', '05');
    if ($rekFrom['status_nas'] !== 'A') json_err('Nasabah tidak aktif', '05');

    // Cek info bank
    $bank = DB::first("SELECT * FROM gcore_bankcode WHERE bank_code = ?", [$toBankCode]);
    if (!$bank) json_err('Kode bank tidak ditemukan', '04', 404);

    $biayaAdmin = (float)$bank['transfer_cost'];
    $totalDebit = $jumlah + $biayaAdmin;
    $saldo      = (float)$rekFrom['saldo'];

    if (($saldo - $totalDebit) < SALDO_MIN) {
        json_err(
            'Saldo tidak cukup. Saldo: ' . rp($saldo) .
            ' | Dibutuhkan: ' . rp($totalDebit) .
            ' (nominal + biaya admin ' . rp($biayaAdmin) . ')' .
            ' | Min mengendap: ' . rp(SALDO_MIN),
            '51'
        );
    }

    DB::begin();
    $transNo = trans_no('AB');

    // Debit dari rekening (nominal + biaya admin)
    insert_folio(
        $fromRek, $totalDebit, 0,
        "Transfer ke {$bank['bank_name']} {$toBankAcc} " . ($toBankName ? "- $toBankName" : '') . " | $ket",
        'AB', 'system'
    );

    $saldoBaru = get_saldo($fromRek);

    // Log transfer
    DB::run(
        "INSERT INTO gmob_transfer
            (trans_date, jenis, from_norek, from_name, to_norek, to_name, bank_code, bank_name,
             amount, cost, balance, remark, trans_no, ref_no, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'00')",
        [now_iso(), 'BANK', $fromRek, $rekFrom['nama'],
         $toBankAcc, $toBankName, $toBankCode, $bank['bank_name'],
         $jumlah, $biayaAdmin, $saldoBaru,
         $ket, $transNo, $refNo]
    );

    DB::commit();

    log_trans($fromRek, 'TRANSFER-BANK', "Ke {$bank['bank_name']} {$toBankAcc}", $totalDebit);

    json_ok([
        'message'      => 'Transfer ke bank berhasil',
        'trans_no'     => $transNo,
        'from_norek'   => $fromRek,
        'from_name'    => $rekFrom['nama'],
        'to_bank_code' => $toBankCode,
        'to_bank_name' => $bank['bank_name'],
        'to_bank_acc'  => $toBankAcc,
        'to_name'      => $toBankName,
        'jumlah'       => $jumlah,
        'jumlah_fmt'   => rp($jumlah),
        'biaya_admin'  => $biayaAdmin,
        'biaya_fmt'    => rp($biayaAdmin),
        'total_debit'  => $totalDebit,
        'total_fmt'    => rp($totalDebit),
        'saldo'        => $saldoBaru,
        'saldo_fmt'    => rp($saldoBaru),
        'ref_no'       => $refNo,
        'note'         => 'Transfer berhasil (simulasi)',
    ]);
} catch (Exception $e) {
    DB::rollback();
    error_log('[transfer_bank] ' . $e->getMessage());
    json_err('Gagal transfer: ' . $e->getMessage(), '99', 500);
}
