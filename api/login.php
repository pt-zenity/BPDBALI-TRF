<?php
/**
 * POST /api/login  - Login nasabah / admin
 * Kompatibel: PHP 7.0 - PHP 8.x
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b        = get_body();
$username = trim(isset($b['username'])   ? $b['username']   : '');
$password = trim(isset($b['password'])   ? $b['password']   : (isset($b['pass_crypto']) ? $b['pass_crypto'] : ''));

if (!$username || !$password) json_err('Username dan password wajib', '01');

try {
    $user = DB::first(
        "SELECT n.*, r.saldo, r.notab, r.produk, r.status as status_rek
         FROM gmob_nasabah n
         LEFT JOIN gmob_rekening r ON r.notab = n.norek
         WHERE n.username = ?",
        array($username)
    );

    if (!$user) json_err('Username tidak ditemukan', '03', 401);
    if (!verify_pass($password, $user['pass_crypto'])) json_err('Password salah', '03', 401);
    if ($user['status'] === 'B') json_err('Akun diblokir. Hubungi petugas LPD.', '07', 403);
    if ($user['status'] === 'T') json_err('Akun sudah ditutup.', '07', 403);

    $saldo     = isset($user['saldo']) ? (float)$user['saldo'] : 0.0;
    $saldoFmt  = rp($saldo);

    json_ok(array(
        'message' => 'Login berhasil',
        'user'    => array(
            'id'        => $user['id'],
            'noid'      => $user['noid'],
            'nama'      => $user['nama'],
            'norek'     => $user['norek'],
            'username'  => $user['username'],
            'phone'     => $user['phone'],
            'email'     => $user['email'],
            'status'    => $user['status'],
            'saldo'     => $saldo,
            'saldo_fmt' => $saldoFmt,
        )
    ));
} catch (Exception $e) {
    error_log('[login] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
