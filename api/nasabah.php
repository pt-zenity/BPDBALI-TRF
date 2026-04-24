<?php
/**
 * /api/nasabah  - CRUD Nasabah
 * GET    /api/nasabah             -> list semua
 * GET    /api/nasabah/{id}        -> detail 1
 * POST   /api/nasabah             -> tambah baru
 * PUT    /api/nasabah/{id}/status -> ubah status
 * DELETE /api/nasabah/{id}        -> hapus
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts  = array_values(array_filter(explode('/', $uri)));
// parts: [0:api, 1:nasabah, 2:id?, 3:action?]
$id     = isset($parts[2]) ? (int)$parts[2] : null;
$action = $parts[3] ?? null;

try {
    // ----------------------------------------
    // GET /api/nasabah  (list)
    // ----------------------------------------
    if ($method === 'GET' && $id === null) {
        $search = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';

        $where  = [];
        $params = [];

        if ($search) {
            $where[]  = "(nama LIKE ? OR noid LIKE ? OR norek LIKE ? OR username LIKE ?)";
            $like     = "%$search%";
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($status) {
            $where[]  = "status = ?";
            $params[] = $status;
        }

        $sql  = "SELECT id, noid, nama, norek, username, phone, email, status, created_at FROM gmob_nasabah";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY id DESC";

        $rows = DB::all($sql, $params);
        json_ok(['data' => $rows, 'total' => count($rows)]);
    }

    // ----------------------------------------
    // GET /api/nasabah/{id}  (detail)
    // ----------------------------------------
    elseif ($method === 'GET' && $id !== null) {
        $row = DB::first(
            "SELECT n.*, r.saldo, r.notab, r.produk, r.status as status_rek
             FROM gmob_nasabah n
             LEFT JOIN gmob_rekening r ON r.notab = n.norek
             WHERE n.id = ?",
            [$id]
        );
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        // Ambil histori 5 transaksi terakhir
        $histori = DB::all(
            "SELECT * FROM gtb_folio WHERE linker = ? ORDER BY id DESC LIMIT 5",
            [$row['norek']]
        );
        $row['histori'] = $histori;

        json_ok(['data' => $row]);
    }

    // ----------------------------------------
    // POST /api/nasabah  (tambah baru)
    // ----------------------------------------
    elseif ($method === 'POST') {
        $b = get_body();

        $nama     = trim($b['nama']     ?? '');
        $username = trim($b['username'] ?? '');
        $pass     = trim($b['pass_crypto'] ?? $b['password'] ?? '');
        $pin      = trim($b['pin_crypto']  ?? $b['pin']      ?? '123456');
        $phone    = trim($b['phone']    ?? '');
        $email    = trim($b['email']    ?? '');
        $alamat   = trim($b['alamat']   ?? '');

        if (!$nama || !$username || !$pass)
            json_err('Data tidak lengkap (nama, username, password wajib)', '01');

        if (strlen($pass) < 6)
            json_err('Password minimal 6 karakter', '01');

        // Cek duplikat username
        $exist = DB::first("SELECT id FROM gmob_nasabah WHERE username = ?", [$username]);
        if ($exist) json_err('Username sudah terdaftar', '02', 409);

        // Generate noid & norek otomatis
        $noid  = gen_noid('CG');
        $norek = gen_norek();

        // Pastikan noid unik
        while (DB::first("SELECT id FROM gmob_nasabah WHERE noid = ?", [$noid])) {
            $noid = 'CG.' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        }

        $now = now_iso();
        DB::begin();
        try {
            DB::run(
                "INSERT INTO gmob_nasabah
                    (noid, nama, norek, username, pass_crypto, pin_crypto, phone, email, alamat, status, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,'R',?)",
                [$noid, $nama, $norek, $username, $pass, $pin, $phone, $email, $alamat, $now]
            );

            // Rekening tabungan
            DB::run(
                "INSERT INTO gmob_rekening (noid, notab, nama, produk, saldo, status, created_at)
                 VALUES (?,?,?,'Tabungan',0,'A',?)",
                [$noid, $norek, $nama, $now]
            );

            // gtb_nasabah (core banking)
            DB::run(
                "INSERT OR IGNORE INTO gtb_nasabah (linker, nasabah, status) VALUES (?,?,'A')",
                [$norek, $nama]
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        json_ok([
            'message' => 'Nasabah berhasil didaftarkan',
            'noid'    => $noid,
            'norek'   => $norek,
            'nama'    => $nama,
            'status'  => 'R',
            'note'    => 'Status Registrasi. Aktifkan nasabah untuk mulai bertransaksi.',
        ]);
    }

    // ----------------------------------------
    // PUT /api/nasabah/{id}/status  (ubah status)
    // ----------------------------------------
    elseif ($method === 'PUT' && $action === 'status') {
        $b      = get_body();
        $status = strtoupper($b['status'] ?? '');
        if (!in_array($status, ['A', 'R', 'B', 'T']))
            json_err('Status tidak valid. Gunakan: A (Aktif), R (Registrasi), B (Blokir), T (Tutup)', '01');

        $row = DB::first("SELECT id, nama, norek FROM gmob_nasabah WHERE id = ?", [$id]);
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        DB::run("UPDATE gmob_nasabah SET status=?, updated_at=? WHERE id=?", [$status, now_iso(), $id]);

        $statusLabel = ['A'=>'Aktif','R'=>'Registrasi','B'=>'Blokir','T'=>'Tutup'][$status];
        json_ok(['message' => "Status diupdate ke: $statusLabel ({$status})", 'nama' => $row['nama']]);
    }

    // ----------------------------------------
    // PUT /api/nasabah/{id}  (update data)
    // ----------------------------------------
    elseif ($method === 'PUT' && $action === null) {
        $b    = get_body();
        $row  = DB::first("SELECT * FROM gmob_nasabah WHERE id = ?", [$id]);
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        $nama   = trim($b['nama']   ?? $row['nama']);
        $phone  = trim($b['phone']  ?? $row['phone']);
        $email  = trim($b['email']  ?? $row['email']);
        $alamat = trim($b['alamat'] ?? $row['alamat']);

        DB::run(
            "UPDATE gmob_nasabah SET nama=?, phone=?, email=?, alamat=?, updated_at=? WHERE id=?",
            [$nama, $phone, $email, $alamat, now_iso(), $id]
        );
        DB::run("UPDATE gmob_rekening SET nama=? WHERE noid=?", [$nama, $row['noid']]);
        DB::run("UPDATE gtb_nasabah SET nasabah=? WHERE linker=?", [$nama, $row['norek']]);

        json_ok(['message' => 'Data nasabah diperbarui', 'nama' => $nama]);
    }

    // ----------------------------------------
    // DELETE /api/nasabah/{id}
    // ----------------------------------------
    elseif ($method === 'DELETE' && $id !== null) {
        $row = DB::first("SELECT noid, norek, nama FROM gmob_nasabah WHERE id = ?", [$id]);
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        // Cek saldo
        $saldo = get_saldo($row['norek']);
        if ($saldo > 0) json_err('Tidak dapat hapus nasabah dengan saldo tersisa: ' . rp($saldo), '06', 400);

        DB::begin();
        try {
            DB::run("DELETE FROM gmob_nasabah  WHERE id=?",        [$id]);
            DB::run("DELETE FROM gmob_rekening WHERE noid=?",      [$row['noid']]);
            DB::run("DELETE FROM gtb_nasabah   WHERE linker=?",    [$row['norek']]);
            DB::run("DELETE FROM gtb_folio     WHERE linker=?",    [$row['norek']]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        json_ok(['message' => 'Nasabah ' . $row['nama'] . ' berhasil dihapus']);
    }

    else {
        json_err('Endpoint tidak ditemukan', '404', 404);
    }

} catch (Exception $e) {
    error_log('[nasabah] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
