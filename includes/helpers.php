<?php
/**
 * LPD Canggu - Helper Functions
 * Kompatibel: PHP 7.0 - PHP 8.x
 */

// ---- Response ----

/**
 * @param  array $data
 * @return void
 */
function json_ok($data = array())
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        array_merge(array('status' => '00'), $data),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
    exit;
}

/**
 * @param  string $message
 * @param  string $code
 * @param  int    $http
 * @return void
 */
function json_err($message, $code = '99', $http = 400)
{
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        array('status' => $code, 'message' => $message),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// ---- Request ----

/**
 * @return array
 */
function get_body()
{
    $raw = file_get_contents('php://input');
    if (!$raw) return array();
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

/**
 * @param  string $key
 * @param  mixed  $default
 * @return mixed
 */
function get_param($key, $default = null)
{
    if (isset($_GET[$key]))  return $_GET[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

// ---- CORS ----

/**
 * @return void
 */
function cors()
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ---- Nomor & Kode ----

/**
 * @param  string $prefix
 * @return string
 */
function trans_no($prefix = 'TR')
{
    return $prefix . date('ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

/**
 * @param  string $prefix
 * @return string
 */
function gen_noid($prefix = 'CG')
{
    $seq = (int) DB::scalar("SELECT COUNT(*) FROM gmob_nasabah") + 1;
    return $prefix . '.' . str_pad($seq, 9, '0', STR_PAD_LEFT);
}

/**
 * @return string
 */
function gen_norek()
{
    $count = (int) DB::scalar("SELECT COUNT(*) FROM gmob_rekening") + 1;
    $grp   = str_pad((int)($count / 1000) + 1, 2, '0', STR_PAD_LEFT);
    $seq   = str_pad($count % 1000, 6, '0', STR_PAD_LEFT);
    $norek = $grp . '.' . $seq;

    // Jika sudah ada, fallback ke random
    $exist = DB::first("SELECT id FROM gmob_rekening WHERE notab=?", array($norek));
    if ($exist) {
        $norek = str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT)
               . '.'
               . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }
    return $norek;
}

/**
 * @return string
 */
function now_iso()
{
    return date('Y-m-d H:i:s');
}

/**
 * @return string
 */
function today()
{
    return date('Y-m-d');
}

// ---- Format ----

/**
 * @param  float $n
 * @return string
 */
function rp($n)
{
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

/**
 * @param  string $tgl
 * @return string
 */
function fmt_tgl($tgl)
{
    if (!$tgl) return '-';
    $ts = strtotime($tgl);
    return $ts ? date('d/m/Y H:i', $ts) : $tgl;
}

// ---- Saldo & Rekening ----

/**
 * @param  string $norek
 * @return float
 */
function get_saldo($norek)
{
    $row = DB::first("SELECT saldo FROM gmob_rekening WHERE notab=?", array($norek));
    return isset($row['saldo']) ? (float)$row['saldo'] : 0.0;
}

/**
 * @param  string $norek
 * @param  float  $saldo
 * @return void
 */
function update_saldo($norek, $saldo)
{
    DB::run("UPDATE gmob_rekening SET saldo=? WHERE notab=?", array($saldo, $norek));
}

/**
 * @param  string $norek
 * @return array|null
 */
function get_nasabah_by_norek($norek)
{
    return DB::first("SELECT * FROM gtb_nasabah WHERE linker=?", array($norek));
}

/**
 * @param  string $norek
 * @return array|null
 */
function get_rekening_info($norek)
{
    return DB::first(
        "SELECT r.*, n.username, n.phone, n.status as status_nasabah
         FROM gmob_rekening r
         LEFT JOIN gmob_nasabah n ON n.norek = r.notab
         WHERE r.notab = ?",
        array($norek)
    );
}

// ---- Transaksi ----

/**
 * Insert satu entri folio (buku besar) dan update saldo rekening
 * @param  string $norek
 * @param  float  $debit
 * @param  float  $credit
 * @param  string $remark
 * @param  string $transCode
 * @param  string $userid
 * @return string nomor transaksi
 */
function insert_folio($norek, $debit, $credit, $remark, $transCode = 'ST', $userid = 'system')
{
    $transNo   = trans_no($transCode);
    $now       = now_iso();
    $saldoLama = get_saldo($norek);
    $saldoBaru = $saldoLama + (float)$credit - (float)$debit;

    DB::run(
        "INSERT INTO gtb_folio
            (linker, trans_date, mutasi_date, trans_code, debit, credit, remark, trans_no, bill_no, userid, saldo, debit_val)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
        array(
            $norek, $now, today(), $transCode,
            (float)$debit, (float)$credit, $remark, $transNo,
            str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            $userid, $saldoBaru,
            ($debit > 0 ? 'T' : 'F')
        )
    );

    update_saldo($norek, $saldoBaru);

    return $transNo;
}

// ---- Log Transaksi ----

/**
 * @param  string $norek
 * @param  string $jenis
 * @param  string $ket
 * @param  float  $amount
 * @param  string $status
 * @return void
 */
function log_trans($norek, $jenis, $ket, $amount, $status = '00')
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    DB::run(
        "INSERT INTO gmob_log_trans (norek, jenis, keterangan, amount, status, ip_addr) VALUES (?,?,?,?,?,?)",
        array($norek, $jenis, $ket, (float)$amount, $status, $ip)
    );
}

// ---- Security ----

/**
 * @param  string $pass
 * @return string
 */
function hash_pass($pass)
{
    return password_hash($pass, PASSWORD_DEFAULT);
}

/**
 * Verifikasi password — support plain text (legacy) dan bcrypt
 * @param  string $pass
 * @param  string $hash
 * @return bool
 */
function verify_pass($pass, $hash)
{
    // Plain text (backward compat data lama)
    if ($pass === $hash) return true;
    return password_verify($pass, $hash);
}
