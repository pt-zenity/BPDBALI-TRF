<?php
/**
 * LPD Canggu - Helper Functions
 */

// ---- Response ----
function json_ok(array $data = []): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['status' => '00'], $data), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function json_err(string $message, string $code = '99', int $http = 400): void {
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => $code, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// ---- Request ----
function get_body(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function get_param(string $key, mixed $default = null): mixed {
    return $_GET[$key] ?? $_POST[$key] ?? $default;
}

// ---- CORS ----
function cors(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ---- Nomor & Kode ----
function trans_no(string $prefix = 'TR'): string {
    return $prefix . date('ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function gen_noid(string $prefix = 'CG'): string {
    // Format: CG.XXXXXXXX
    $seq = DB::scalar("SELECT COUNT(*) FROM gmob_nasabah") + 1;
    return $prefix . '.' . str_pad($seq, 9, '0', STR_PAD_LEFT);
}

function gen_norek(): string {
    // Format: XX.XXXXXX (misal 01.000001)
    $count = DB::scalar("SELECT COUNT(*) FROM gmob_rekening") + 1;
    $grp   = str_pad(intdiv($count, 1000) + 1, 2, '0', STR_PAD_LEFT);
    $seq   = str_pad($count % 1000, 6, '0', STR_PAD_LEFT);
    // Cek kalau norek sudah ada
    $norek = $grp . '.' . $seq;
    $exist = DB::first("SELECT id FROM gmob_rekening WHERE notab=?", [$norek]);
    if ($exist) {
        // Fallback ke random
        $norek = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT) . '.' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
    return $norek;
}

function now_iso(): string { return date('Y-m-d H:i:s'); }
function today(): string   { return date('Y-m-d'); }

// ---- Format ----
function rp(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function fmt_tgl(string $tgl): string {
    if (!$tgl) return '-';
    $ts = strtotime($tgl);
    return $ts ? date('d/m/Y H:i', $ts) : $tgl;
}

// ---- Saldo & Rekening ----
function get_saldo(string $norek): float {
    // Ambil dari gmob_rekening (lebih cepat)
    $row = DB::first("SELECT saldo FROM gmob_rekening WHERE notab=?", [$norek]);
    return (float)($row['saldo'] ?? 0);
}

function update_saldo(string $norek, float $saldo): void {
    DB::run("UPDATE gmob_rekening SET saldo=? WHERE notab=?", [$saldo, $norek]);
}

function get_nasabah_by_norek(string $norek): ?array {
    return DB::first("SELECT * FROM gtb_nasabah WHERE linker=?", [$norek]);
}

function get_rekening_info(string $norek): ?array {
    return DB::first(
        "SELECT r.*, n.username, n.phone, n.status as status_nasabah
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        [$norek]
    );
}

// ---- Transaksi ----
function insert_folio(
    string $norek,
    float  $debit,
    float  $credit,
    string $remark,
    string $transCode = 'ST',
    string $userid    = 'system'
): string {
    $transNo  = trans_no($transCode);
    $now      = now_iso();
    $saldoLama = get_saldo($norek);
    $saldoBaru = $saldoLama + $credit - $debit;

    DB::run(
        "INSERT INTO gtb_folio
            (linker, trans_date, mutasi_date, trans_code, debit, credit, remark, trans_no, bill_no, userid, saldo, debit_val)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
        [$norek, $now, today(), $transCode, $debit, $credit, $remark, $transNo,
         str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
         $userid, $saldoBaru, ($debit > 0 ? 'T' : 'F')]
    );

    // Update saldo di gmob_rekening
    update_saldo($norek, $saldoBaru);

    return $transNo;
}

// ---- Log Transaksi ----
function log_trans(string $norek, string $jenis, string $ket, float $amount, string $status = '00'): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    DB::run(
        "INSERT INTO gmob_log_trans (norek, jenis, keterangan, amount, status, ip_addr) VALUES (?,?,?,?,?,?)",
        [$norek, $jenis, $ket, $amount, $status, $ip]
    );
}

// ---- Security ----
function hash_pass(string $pass): string {
    return password_hash($pass, PASSWORD_DEFAULT);
}

function verify_pass(string $pass, string $hash): bool {
    // Support plain text (untuk backward compat dengan legacy data)
    if ($pass === $hash) return true;
    return password_verify($pass, $hash);
}
