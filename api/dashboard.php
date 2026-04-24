<?php
/**
 * GET /api/dashboard  - Statistik dashboard
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

try {
    $today = today();

    $totalNasabah  = (int) DB::scalar("SELECT COUNT(*) FROM gmob_nasabah");
    $aktifNasabah  = (int) DB::scalar("SELECT COUNT(*) FROM gmob_nasabah WHERE status='A'");
    $baruNasabah   = (int) DB::scalar("SELECT COUNT(*) FROM gmob_nasabah WHERE DATE(created_at)=?", array($today));

    $rawSaldo      = DB::scalar("SELECT COALESCE(SUM(saldo),0) FROM gmob_rekening");
    $totalSaldo    = (float)($rawSaldo !== null ? $rawSaldo : 0);

    $totalTrans    = (int) DB::scalar("SELECT COUNT(*) FROM gtb_folio");
    $transHariIni  = (int) DB::scalar("SELECT COUNT(*) FROM gtb_folio WHERE DATE(trans_date)=?",  array($today));

    $rawDebit      = DB::scalar("SELECT COALESCE(SUM(debit),0)  FROM gtb_folio WHERE DATE(trans_date)=?", array($today));
    $rawKredit     = DB::scalar("SELECT COALESCE(SUM(credit),0) FROM gtb_folio WHERE DATE(trans_date)=?", array($today));
    $debitHariIni  = (float)($rawDebit  !== null ? $rawDebit  : 0);
    $kreditHariIni = (float)($rawKredit !== null ? $rawKredit : 0);

    $transferHariIni = (int) DB::scalar("SELECT COUNT(*) FROM gmob_transfer WHERE DATE(trans_date)=?", array($today));
    $rawNilaiTrf     = DB::scalar("SELECT COALESCE(SUM(amount),0) FROM gmob_transfer WHERE DATE(trans_date)=?", array($today));
    $nilaiTransfer   = (float)($rawNilaiTrf !== null ? $rawNilaiTrf : 0);

    // Transaksi terbaru
    $recentTrans = DB::all(
        "SELECT f.*, r.nama FROM gtb_folio f
         LEFT JOIN gmob_rekening r ON r.notab = f.linker
         ORDER BY f.id DESC LIMIT 10"
    );
    foreach ($recentTrans as $i => $t) {
        $recentTrans[$i]['debit_fmt']  = rp((float)$t['debit']);
        $recentTrans[$i]['credit_fmt'] = rp((float)$t['credit']);
        $recentTrans[$i]['saldo_fmt']  = rp((float)$t['saldo']);
        $recentTrans[$i]['tgl_fmt']    = fmt_tgl($t['trans_date']);
        $recentTrans[$i]['jenis']      = (float)$t['debit'] > 0 ? 'Debit' : 'Kredit';
    }

    // Nasabah terbaru
    $recentNasabah = DB::all(
        "SELECT id, noid, nama, norek, status, created_at FROM gmob_nasabah ORDER BY id DESC LIMIT 5"
    );

    // Transfer terbaru
    $recentTransfer = DB::all(
        "SELECT * FROM gmob_transfer ORDER BY id DESC LIMIT 5"
    );
    foreach ($recentTransfer as $i => $tr) {
        $recentTransfer[$i]['amount_fmt'] = rp((float)$tr['amount']);
        $recentTransfer[$i]['tgl_fmt']    = fmt_tgl($tr['trans_date']);
    }

    json_ok(array(
        'stats' => array(
            'total_nasabah'      => $totalNasabah,
            'aktif_nasabah'      => $aktifNasabah,
            'baru_hari_ini'      => $baruNasabah,
            'total_saldo'        => $totalSaldo,
            'total_saldo_fmt'    => rp($totalSaldo),
            'total_trans'        => $totalTrans,
            'trans_hari_ini'     => $transHariIni,
            'debit_hari_ini'     => $debitHariIni,
            'debit_fmt'          => rp($debitHariIni),
            'kredit_hari_ini'    => $kreditHariIni,
            'kredit_fmt'         => rp($kreditHariIni),
            'transfer_hari_ini'  => $transferHariIni,
            'nilai_transfer'     => $nilaiTransfer,
            'nilai_transfer_fmt' => rp($nilaiTransfer),
        ),
        'recent_transactions' => $recentTrans,
        'recent_nasabah'      => $recentNasabah,
        'recent_transfer'     => $recentTransfer,
        'tanggal'             => date('d F Y'),
        'waktu'               => date('H:i:s'),
    ));
} catch (Exception $e) {
    error_log('[dashboard] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
