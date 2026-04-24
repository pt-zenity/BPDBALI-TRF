<?php
/**
 * LPD Canggu - PHP Router
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
    if (file_exists($file)) return false; // biarkan built-in server handle
    http_response_code(404);
    exit;
}

// Route tabel API
$routes = [
    // [METHOD_REGEX, URI_REGEX, handler]
    ['POST',        '#^/api/init-db$#',                    'api/init.php'],
    ['GET|POST',    '#^/api/nasabah$#',                    'api/nasabah.php'],
    ['GET|PUT|DELETE', '#^/api/nasabah/\d+$#',            'api/nasabah.php'],
    ['PUT',         '#^/api/nasabah/\d+/status$#',         'api/nasabah.php'],
    ['GET',         '#^/api/rekening/[^/]+$#',             'api/rekening.php'],
    ['GET',         '#^/api/saldo/[^/]+$#',                'api/saldo.php'],
    ['GET',         '#^/api/mutasi/[^/]+$#',               'api/mutasi.php'],
    ['POST',        '#^/api/setor$#',                      'api/setor.php'],
    ['POST',        '#^/api/tarik$#',                      'api/tarik.php'],
    ['POST',        '#^/api/transfer-lpd$#',               'api/transfer_lpd.php'],
    ['POST',        '#^/api/transfer-bank$#',              'api/transfer_bank.php'],
    ['GET',         '#^/api/bank-list$#',                  'api/bank_list.php'],
    ['GET',         '#^/api/dashboard$#',                  'api/dashboard.php'],
    ['POST',        '#^/api/login$#',                      'api/login.php'],
    ['GET',         '#^/api/riwayat-transfer/[^/]+$#',     'api/riwayat_transfer.php'],
];

foreach ($routes as [$methods, $pattern, $handler]) {
    if (preg_match("#^($methods)$#", $method) && preg_match($pattern, $uri)) {
        require __DIR__ . '/' . $handler;
        return true;
    }
}

// Semua request non-API → halaman utama
if (!str_starts_with($uri, '/api/')) {
    require __DIR__ . '/index.php';
    return true;
}

// 404 untuk API tidak dikenal
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['status' => '404', 'message' => 'Endpoint tidak ditemukan: ' . $uri]);
