<?php
/**
 * GET /api/mutasi/{norek}  - Mutasi rekening / riwayat transaksi
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $uri)));
$norek = urldecode($parts[2] ?? '');

if (!$norek) json_err('No rekening wajib', '01');

$limit    = (int)($_GET['limit']      ?? 50);
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date']   ?? '';
$jenis     = $_GET['jenis']      ?? ''; // debit / kredit

try {
    // Cek rekening
    $rek = DB::first(
        "SELECT r.nama, r.saldo, r.status, n.nama as nama_nas
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$norek]
    );
    if (!$rek) json_err('Rekening tidak ditemukan', '04', 404);

    // Build query
    $where  = ['linker = ?'];
    $params = [$norek];

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

    $sql = "SELECT id, trans_date, mutasi_date, trans_code, debit, credit, remark, trans_no, saldo
            FROM gtb_folio
            WHERE " . implode(' AND ', $where) .
            " ORDER BY id DESC LIMIT ?";
    $params[] = $limit;

    $rows = DB::all($sql, $params);

    // Format
    foreach ($rows as &$r) {
        $r['debit_fmt']  = rp((float)$r['debit']);
        $r['credit_fmt'] = rp((float)$r['credit']);
        $r['saldo_fmt']  = rp((float)$r['saldo']);
        $r['tgl_fmt']    = fmt_tgl($r['trans_date']);
        $r['jenis']      = (float)$r['debit'] > 0 ? 'D' : 'K';
    }
    unset($r);

    json_ok([
        'norek'      => $norek,
        'nama'       => $rek['nama'],
        'saldo'      => (float)$rek['saldo'],
        'saldo_fmt'  => rp((float)$rek['saldo']),
        'data'       => $rows,
        'total'      => count($rows),
    ]);
} catch (Exception $e) {
    error_log('[mutasi] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
