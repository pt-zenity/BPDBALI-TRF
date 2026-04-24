<?php
/**
 * /api/nasabah  - CRUD Nasabah
 * Kompatibel: PHP 7.0 - PHP 8.x
 *
 * GET    /api/nasabah             -> list semua
 * GET    /api/nasabah/{id}        -> detail 1
 * POST   /api/nasabah             -> tambah baru
 * PUT    /api/nasabah/{id}        -> update data
 * PUT    /api/nasabah/{id}/status -> ubah status
 * DELETE /api/nasabah/{id}        -> hapus
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts  = array_values(array_filter(explode('/', $uri)));
// parts: [0:api, 1:nasabah, 2:id?, 3:action?]
$id     = isset($parts[2]) ? (int)$parts[2] : null;
$action = isset($parts[3]) ? $parts[3] : null;

try {

    // ----------------------------------------
    // GET /api/nasabah  (list)
    // ----------------------------------------
    if ($method === 'GET' && $id === null) {
        $search = isset($_GET['q']) ? $_GET['q'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        $where  = array();
        $params = array();

        if ($search) {
            $where[]  = "(nama LIKE ? OR noid LIKE ? OR norek LIKE ? OR username LIKE ?)";
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status) {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        $sql = "SELECT id, noid, nama, norek, username, phone, email, status, created_at FROM gmob_nasabah";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY id DESC';

        $rows = DB::all($sql, $params);
        json_ok(array('data' => $rows, 'total' => count($rows)));
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
            array($id)
        );
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        $histori = DB::all(
            "SELECT * FROM gtb_folio WHERE linker = ? ORDER BY id DESC LIMIT 5",
            array($row['norek'])
        );
        $row['histori'] = $histori;

        json_ok(array('data' => $row));
    }

    // ----------------------------------------
    // POST /api/nasabah  (tambah baru)
    // ----------------------------------------
    elseif ($method === 'POST') {
        $b = get_body();

        $nama     = trim(isset($b['nama'])        ? $b['nama']        : '');
        $username = trim(isset($b['username'])     ? $b['username']    : '');
        $pass     = trim(isset($b['pass_crypto'])  ? $b['pass_crypto'] : (isset($b['password']) ? $b['password'] : ''));
        $pin      = trim(isset($b['pin_crypto'])   ? $b['pin_crypto']  : (isset($b['pin'])      ? $b['pin']      : '123456'));
        $phone    = trim(isset($b['phone'])        ? $b['phone']       : '');
        $email    = trim(isset($b['email'])        ? $b['email']       : '');
        $alamat   = trim(isset($b['alamat'])       ? $b['alamat']      : '');

        if (!$nama || !$username || !$pass) {
            json_err('Data tidak lengkap (nama, username, password wajib)', '01');
        }
        if (strlen($pass) < 6) {
            json_err('Password minimal 6 karakter', '01');
        }

        $exist = DB::first("SELECT id FROM gmob_nasabah WHERE username = ?", array($username));
        if ($exist) json_err('Username sudah terdaftar', '02', 409);

        $noid  = gen_noid('CG');
        $norek = gen_norek();

        while (DB::first("SELECT id FROM gmob_nasabah WHERE noid = ?", array($noid))) {
            $noid = 'CG.' . str_pad(mt_rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        }

        $now = now_iso();
        DB::begin();
        try {
            DB::run(
                "INSERT INTO gmob_nasabah
                    (noid, nama, norek, username, pass_crypto, pin_crypto, phone, email, alamat, status, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,'R',?)",
                array($noid, $nama, $norek, $username, $pass, $pin, $phone, $email, $alamat, $now)
            );
            DB::run(
                "INSERT INTO gmob_rekening (noid, notab, nama, produk, saldo, status, created_at)
                 VALUES (?,?,?,'Tabungan',0,'A',?)",
                array($noid, $norek, $nama, $now)
            );
            DB::run(
                "INSERT OR IGNORE INTO gtb_nasabah (linker, nasabah, status) VALUES (?,?,'A')",
                array($norek, $nama)
            );
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }

        json_ok(array(
            'message' => 'Nasabah berhasil didaftarkan',
            'noid'    => $noid,
            'norek'   => $norek,
            'nama'    => $nama,
            'status'  => 'R',
            'note'    => 'Status Registrasi. Aktifkan nasabah untuk mulai bertransaksi.',
        ));
    }

    // ----------------------------------------
    // PUT /api/nasabah/{id}/status  (ubah status)
    // ----------------------------------------
    elseif ($method === 'PUT' && $action === 'status') {
        $b      = get_body();
        $status = strtoupper(isset($b['status']) ? $b['status'] : '');
        if (!in_array($status, array('A', 'R', 'B', 'T'), true)) {
            json_err('Status tidak valid. Gunakan: A (Aktif), R (Registrasi), B (Blokir), T (Tutup)', '01');
        }

        $row = DB::first("SELECT id, nama, norek FROM gmob_nasabah WHERE id = ?", array($id));
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        DB::run("UPDATE gmob_nasabah SET status=?, updated_at=? WHERE id=?", array($status, now_iso(), $id));

        $labels = array('A' => 'Aktif', 'R' => 'Registrasi', 'B' => 'Blokir', 'T' => 'Tutup');
        json_ok(array(
            'message' => 'Status diupdate ke: ' . $labels[$status] . ' (' . $status . ')',
            'nama'    => $row['nama'],
        ));
    }

    // ----------------------------------------
    // PUT /api/nasabah/{id}  (update data)
    // ----------------------------------------
    elseif ($method === 'PUT' && $action === null) {
        $b   = get_body();
        $row = DB::first("SELECT * FROM gmob_nasabah WHERE id = ?", array($id));
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        $nama   = trim(isset($b['nama'])   ? $b['nama']   : $row['nama']);
        $phone  = trim(isset($b['phone'])  ? $b['phone']  : $row['phone']);
        $email  = trim(isset($b['email'])  ? $b['email']  : $row['email']);
        $alamat = trim(isset($b['alamat']) ? $b['alamat'] : $row['alamat']);

        DB::run(
            "UPDATE gmob_nasabah SET nama=?, phone=?, email=?, alamat=?, updated_at=? WHERE id=?",
            array($nama, $phone, $email, $alamat, now_iso(), $id)
        );
        DB::run("UPDATE gmob_rekening SET nama=? WHERE noid=?", array($nama, $row['noid']));
        DB::run("UPDATE gtb_nasabah SET nasabah=? WHERE linker=?", array($nama, $row['norek']));

        json_ok(array('message' => 'Data nasabah diperbarui', 'nama' => $nama));
    }

    // ----------------------------------------
    // DELETE /api/nasabah/{id}
    // ----------------------------------------
    elseif ($method === 'DELETE' && $id !== null) {
        $row = DB::first("SELECT noid, norek, nama FROM gmob_nasabah WHERE id = ?", array($id));
        if (!$row) json_err('Nasabah tidak ditemukan', '04', 404);

        $saldo = get_saldo($row['norek']);
        if ($saldo > 0) {
            json_err('Tidak dapat hapus nasabah dengan saldo tersisa: ' . rp($saldo), '06', 400);
        }

        DB::begin();
        try {
            DB::run("DELETE FROM gmob_nasabah  WHERE id=?",     array($id));
            DB::run("DELETE FROM gmob_rekening WHERE noid=?",   array($row['noid']));
            DB::run("DELETE FROM gtb_nasabah   WHERE linker=?", array($row['norek']));
            DB::run("DELETE FROM gtb_folio     WHERE linker=?", array($row['norek']));
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }

        json_ok(array('message' => 'Nasabah ' . $row['nama'] . ' berhasil dihapus'));
    }

    else {
        json_err('Endpoint tidak ditemukan', '404', 404);
    }

} catch (Exception $e) {
    error_log('[nasabah] ' . $e->getMessage());
    json_err('Server error: ' . $e->getMessage(), '99', 500);
}
