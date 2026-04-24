<?php
/**
 * LPD Canggu - PHP Router
 * Kompatibel: PHP 7.0 - PHP 8.x
 * Jalankan: php -S 0.0.0.0:8080 router.php
 */

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . ltrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// OPTIONS preflight (CORS)
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

// Static files dari folder public/
if (preg_match('/\.(css|js|png|jpg|jpeg|ico|svg|woff2?|ttf|eot)$/i', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) return false;
    http_response_code(404);
    exit;
}

// Route tabel API [METHOD_REGEX, URI_REGEX, handler]
$routes = array(
    array('POST',            '#^/api/init-db$#',                   'api/init.php'),
    array('GET|POST',        '#^/api/nasabah$#',                   'api/nasabah.php'),
    array('GET|PUT|DELETE',  '#^/api/nasabah/[0-9]+$#',           'api/nasabah.php'),
    array('PUT',             '#^/api/nasabah/[0-9]+/status$#',    'api/nasabah.php'),
    array('GET',             '#^/api/rekening/[^/]+$#',            'api/rekening.php'),
    array('GET',             '#^/api/saldo/[^/]+$#',               'api/saldo.php'),
    array('GET',             '#^/api/mutasi/[^/]+$#',              'api/mutasi.php'),
    array('POST',            '#^/api/setor$#',                     'api/setor.php'),
    array('POST',            '#^/api/tarik$#',                     'api/tarik.php'),
    array('POST',            '#^/api/transfer-lpd$#',              'api/transfer_lpd.php'),
    array('POST',            '#^/api/transfer-bank$#',             'api/transfer_bank.php'),
    array('GET',             '#^/api/bank-list$#',                 'api/bank_list.php'),
    array('GET',             '#^/api/dashboard$#',                 'api/dashboard.php'),
    array('POST',            '#^/api/login$#',                     'api/login.php'),
    array('GET',             '#^/api/riwayat-transfer/[^/]+$#',   'api/riwayat_transfer.php'),
);

// Cek apakah URI dikenal (untuk deteksi 405 vs 404)
$uriMatched  = false;
$routeMatched = false;

foreach ($routes as $route) {
    $methods = $route[0];
    $pattern = $route[1];
    $handler = $route[2];

    if (preg_match($pattern, $uri)) {
        $uriMatched = true;                                   // URI dikenal
        if (preg_match('#^(' . $methods . ')$#', $method)) {
            $routeMatched = true;
            require __DIR__ . '/' . $handler;
            return true;
        }
    }
}

// Semua request non-API -> halaman utama
if (strpos($uri, '/api/') !== 0) {
    require __DIR__ . '/index.php';
    return true;
}

// URI dikenal tapi method salah -> 405 Method Not Allowed
if ($uriMatched) {
    http_response_code(405);
    header('Content-Type: application/json');
    header('Allow: GET, POST, PUT, DELETE, OPTIONS');
    echo json_encode(array(
        'status'  => '405',
        'message' => 'Method ' . $method . ' tidak diizinkan untuk endpoint: ' . $uri,
    ));
    exit;
}

// URI tidak dikenal -> 404 Not Found
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(array(
    'status'  => '404',
    'message' => 'Endpoint tidak ditemukan: ' . $uri,
));
