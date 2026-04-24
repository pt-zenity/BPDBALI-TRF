<?php
/**
 * GET /api/mutasi/{norek}  - Mutasi rekening / riwayat transaksi
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $uri)));
$norek = isset($parts[2]) ? urldecode($parts[2]) : '';

if (!$norek) json_err('No rekening wajib', '01');

$limit     = isset($_GET['limit'])      ? (int)$_GET['limit']      : 50;
$startDate = isset($_GET['start_date']) ? $_GET['start_date']       : '';
$endDate   = isset($_GET['end_date'])   ? $_GET['end_date']         : '';
$jenis     = isset($_GET['jenis'])      ? $_GET['jenis']            : '';

try {
    $rek = DB::first(
        "SELECT r.nama, r.saldo, r.status FROM gmob_rekening r WHERE r.notab = ?",
        array($norek)
    );
    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);

    $where  = array('linker = ?');
    $params = array($norek);

    if ($startDate) {
        $where[]  = 'DATE(trans_date) >= ?';
        $params[] = $startDate;
    }
    if ($endDate) {
        $where[]  = 'DATE(trans_date) <= ?';
        $params[] = $endDate;
    }
    if ($jenis === 'debit') {
        $where[] = 'debit > 0';
    } elseif ($jenis === 'kredit') {
        $where[] = 'credit > 0';
    }

    $params[] = $limit;
    $sql = "SELECT id, trans_date, mutasi_date, trans_code, debit, credit, remark, trans_no, saldo
            FROM gtb_folio
            WHERE " . implode(' AND ', $where) .
            " ORDER BY id DESC LIMIT ?";

    $rows = DB::all($sql, $params);

    foreach ($rows as $i => $r) {
        $rows[$i]['debit_fmt']  = rp((float)$r['debit']);
        $rows[$i]['credit_fmt'] = rp((float)$r['credit']);
        $rows[$i]['saldo_fmt']  = rp((float)$r['saldo']);
        $rows[$i]['tgl_fmt']    = fmt_tgl($r['trans_date']);
        $rows[$i]['jenis']      = (float)$r['debit'] > 0 ? 'D' : 'K';
    }

    json_ok(array(
        'norek'     => $norek,
        'nama'      => $rek['nama'],
        'saldo'     => (float)$rek['saldo'],
        'saldo_fmt' => rp((float)$rek['saldo']),
        'data'      => $rows,
        'total'     => count($rows),
    ));
} catch (Exception $e) {
    error_log('[mutasi] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
