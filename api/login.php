<?php
/**
 * POST /api/login  - Login nasabah / admin
 */
require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method harus POST', '01', 405);

$b        = get_body();
$username = trim($b['username'] ?? '');
$password = trim($b['password'] ?? $b['pass_crypto'] ?? '');

if (!$username || !$password) json_err('Username dan password wajib', '01');

try {
    $user = DB::first(
        "SELECT n.*, r.saldo, r.notab, r.produk, r.status as status_rek
         FROM gmob_nasabah n
         LEFT JOIN gmob_rekening r ON r.notab = n.norek
         WHERE n.username = ?",
        [$username]
    );

    if (!$user) json_err('Username tidak ditemukan', '03', 401);
    if (!verify_pass($password, $user['pass_crypto'])) json_err('Password salah', '03', 401);
    if ($user['status'] === 'B') json_err('Akun diblokir. Hubungi petugas LPD.', '07', 403);
    if ($user['status'] === 'T') json_err('Akun sudah ditutup.', '07', 403);

    json_ok([
        'message'  => 'Login berhasil',
        'user'     => [
            'id'       => $user['id'],
            'noid'     => $user['noid'],
            'nama'     => $user['nama'],
            'norek'    => $user['norek'],
            'username' => $user['username'],
            'phone'    => $user['phone'],
            'email'    => $user['email'],
            'status'   => $user['status'],
            'saldo'    => (float)($user['saldo'] ?? 0),
            'saldo_fmt'=> rp((float)($user['saldo'] ?? 0)),
        ]
    ]);
} catch (Exception $e) {
    error_log('[login] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
