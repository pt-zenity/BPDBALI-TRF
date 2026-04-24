<?php
/**
 * POST /api/transfer-lpd  - Transfer antar rekening dalam LPD
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b        = get_body();
$fromRek  = trim($b['from_norek'] ?? $b['from_acc'] ?? '');
$toRek    = trim($b['to_norek']   ?? $b['to_acc']   ?? '');
$jumlah   = (float)($b['amount']  ?? $b['jumlah'] ?? 0);
$ket      = trim($b['remark']     ?? $b['keterangan'] ?? 'Transfer LPD');
$refNo    = trim($b['ref_no']     ?? trans_no('REF'));

if (!$fromRek) json_err('No rekening pengirim wajib', '01');
if (!$toRek)   json_err('No rekening tujuan wajib',   '01');
if ($fromRek === $toRek) json_err('Rekening pengirim dan tujuan sama', '01');
if ($jumlah < MIN_TRANSFER) json_err('Nominal minimal transfer: ' . rp(MIN_TRANSFER), '01');
if ($jumlah > MAX_TRANSFER) json_err('Nominal maksimal transfer: ' . rp(MAX_TRANSFER), '01');

try {
    // Cek rekening pengirim
    $rekFrom = DB::first(
        "SELECT r.*, n.status as status_nas FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$fromRek]
    );
    if (!$rekFrom) json_err('Rekening pengirim tidak ditemukan', '04', 404);
    if ($rekFrom['status'] !== 'A') json_err('Rekening pengirim tidak aktif', '05');
    if ($rekFrom['status_nas'] !== 'A') json_err('Nasabah pengirim tidak aktif', '05');

    // Cek rekening tujuan
    $rekTo = DB::first(
        "SELECT r.*, n.status as status_nas FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$toRek]
    );
    if (!$rekTo) json_err('Rekening tujuan tidak ditemukan', '04', 404);
    if ($rekTo['status'] !== 'A') json_err('Rekening tujuan tidak aktif', '05');

    // Cek saldo
    $saldo = (float)$rekFrom['saldo'];
    if (($saldo - $jumlah) < SALDO_MIN) {
        json_err(
            'Saldo tidak cukup. Saldo: ' . rp($saldo) .
            ' | Minimum mengendap: ' . rp(SALDO_MIN),
            '51'
        );
    }

    DB::begin();
    $transNo = trans_no('TR');

    // Debit dari pengirim
    insert_folio(
        $fromRek, $jumlah, 0,
        "Transfer ke {$toRek} - {$rekTo['nama']} | $ket",
        'TR', 'system'
    );

    // Kredit ke penerima
    insert_folio(
        $toRek, 0, $jumlah,
        "Transfer dari {$fromRek} - {$rekFrom['nama']} | $ket",
        'TR', 'system'
    );

    // Log transfer
    DB::run(
        "INSERT INTO gmob_transfer
            (trans_date, jenis, from_norek, from_name, to_norek, to_name,
             amount, cost, balance, remark, trans_no, ref_no, status)
         VALUES (?,?,?,?,?,?,?,0,?,?,?,?,'00')",
        [now_iso(), 'LPD', $fromRek, $rekFrom['nama'], $toRek, $rekTo['nama'],
         $jumlah, get_saldo($fromRek), $ket, $transNo, $refNo]
    );

    DB::commit();

    log_trans($fromRek, 'TRANSFER-LPD', "Ke {$toRek}", $jumlah);

    json_ok([
        'message'    => 'Transfer berhasil',
        'trans_no'   => $transNo,
        'from_norek' => $fromRek,
        'from_name'  => $rekFrom['nama'],
        'to_norek'   => $toRek,
        'to_name'    => $rekTo['nama'],
        'jumlah'     => $jumlah,
        'jumlah_fmt' => rp($jumlah),
        'saldo'      => get_saldo($fromRek),
        'saldo_fmt'  => rp(get_saldo($fromRek)),
        'ref_no'     => $refNo,
    ]);
} catch (Exception $e) {
    DB::rollback();
    error_log('[transfer_lpd] ' . $e->getMessage());
    json_err('Gagal transfer: ' . $e->getMessage(), '99', 500);
}
