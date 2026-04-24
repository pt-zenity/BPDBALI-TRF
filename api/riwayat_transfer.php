<?php
/**
 * GET /api/riwayat-transfer/{norek}  - Riwayat transfer
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = array_values(array_filter(explode('/', $uri)));
$norek = isset($parts[2]) ? urldecode($parts[2]) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : ''; // LPD / BANK

if (!$norek) json_err('No rekening wajib', '01');

try {
    $where  = array('(from_norek = ? OR to_norek = ?)');
    $params = array($norek, $norek);

    if ($jenis) {
        $where[]  = 'jenis = ?';
        $params[] = strtoupper($jenis);
    }

    $sql      = "SELECT * FROM gmob_transfer WHERE " . implode(' AND ', $where) . " ORDER BY id DESC LIMIT ?";
    $params[] = $limit;

    $rows = DB::all($sql, $params);

    foreach ($rows as &$r) {
        $r['amount_fmt']  = rp((float)$r['amount']);
        $r['cost_fmt']    = rp((float)$r['cost']);
        $r['balance_fmt'] = rp((float)$r['balance']);
        $r['tgl_fmt']     = fmt_tgl($r['trans_date']);
        $r['arah']        = ($r['from_norek'] === $norek) ? 'keluar' : 'masuk';
    }
    unset($r);

    json_ok(array('data' => $rows, 'total' => count($rows)));
} catch (Exception $e) {
    json_err($e->getMessage(), '99', 500);
}
