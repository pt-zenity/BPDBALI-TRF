<?php
/**
 * GET /api/dashboard  - Statistik dashboard
 */
require_once __DIR__ . '/../includes/bootstrap.php';

try {
    $today = today();

    // Statistik nasabah
    $totalNasabah  = (int)DB::scalar("SELECT COUNT(*) FROM gmob_nasabah");
    $aktifNasabah  = (int)DB::scalar("SELECT COUNT(*) FROM gmob_nasabah WHERE status='A'");
    $baruNasabah   = (int)DB::scalar("SELECT COUNT(*) FROM gmob_nasabah WHERE DATE(created_at)=?", [$today]);

    // Statistik saldo
    $totalSaldo    = (float)(DB::scalar("SELECT COALESCE(SUM(saldo),0) FROM gmob_rekening") ?? 0);

    // Statistik transaksi hari ini
    $totalTrans    = (int)DB::scalar("SELECT COUNT(*) FROM gtb_folio");
    $transHariIni  = (int)DB::scalar("SELECT COUNT(*) FROM gtb_folio WHERE DATE(trans_date)=?", [$today]);
    $debitHariIni  = (float)(DB::scalar("SELECT COALESCE(SUM(debit),0) FROM gtb_folio WHERE DATE(trans_date)=?", [$today]) ?? 0);
    $kreditHariIni = (float)(DB::scalar("SELECT COALESCE(SUM(credit),0) FROM gtb_folio WHERE DATE(trans_date)=?", [$today]) ?? 0);

    // Transfer hari ini
    $transferHariIni = (int)DB::scalar("SELECT COUNT(*) FROM gmob_transfer WHERE DATE(trans_date)=?", [$today]);
    $nilaiTransfer   = (float)(DB::scalar("SELECT COALESCE(SUM(amount),0) FROM gmob_transfer WHERE DATE(trans_date)=?", [$today]) ?? 0);

    // Transaksi terbaru
    $recentTrans = DB::all(
        "SELECT f.*, r.nama FROM gtb_folio f
         LEFT JOIN gmob_rekening r ON r.notab = f.linker
         ORDER BY f.id DESC LIMIT 10"
    );
    foreach ($recentTrans as &$t) {
        $t['debit_fmt']  = rp((float)$t['debit']);
        $t['credit_fmt'] = rp((float)$t['credit']);
        $t['saldo_fmt']  = rp((float)$t['saldo']);
        $t['tgl_fmt']    = fmt_tgl($t['trans_date']);
        $t['jenis']      = (float)$t['debit'] > 0 ? 'Debit' : 'Kredit';
    }
    unset($t);

    // Nasabah terbaru
    $recentNasabah = DB::all(
        "SELECT id, noid, nama, norek, status, created_at FROM gmob_nasabah ORDER BY id DESC LIMIT 5"
    );

    // Transfer terbaru
    $recentTransfer = DB::all(
        "SELECT * FROM gmob_transfer ORDER BY id DESC LIMIT 5"
    );
    foreach ($recentTransfer as &$tr) {
        $tr['amount_fmt'] = rp((float)$tr['amount']);
        $tr['tgl_fmt']    = fmt_tgl($tr['trans_date']);
    }
    unset($tr);

    json_ok([
        'stats' => [
            'total_nasabah'    => $totalNasabah,
            'aktif_nasabah'    => $aktifNasabah,
            'baru_hari_ini'    => $baruNasabah,
            'total_saldo'      => $totalSaldo,
            'total_saldo_fmt'  => rp($totalSaldo),
            'total_trans'      => $totalTrans,
            'trans_hari_ini'   => $transHariIni,
            'debit_hari_ini'   => $debitHariIni,
            'debit_fmt'        => rp($debitHariIni),
            'kredit_hari_ini'  => $kreditHariIni,
            'kredit_fmt'       => rp($kreditHariIni),
            'transfer_hari_ini'=> $transferHariIni,
            'nilai_transfer'   => $nilaiTransfer,
            'nilai_transfer_fmt'=> rp($nilaiTransfer),
        ],
        'recent_transactions' => $recentTrans,
        'recent_nasabah'      => $recentNasabah,
        'recent_transfer'     => $recentTransfer,
        'tanggal'             => date('d F Y'),
        'waktu'               => date('H:i:s'),
    ]);
} catch (Exception $e) {
    error_log('[dashboard] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
