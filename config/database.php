<?php
/**
 * LPD Canggu - Konfigurasi Database
 * Support: SQLite (default lokal) dan SQL Server (sqlsrv - produksi)
 */

// Ambil dari environment atau gunakan .env jika ada
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val, " \t\n\r\"'");
        }
    }
}

define('DB_CONNECTION', $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'sqlite');
define('DB_HOST',       $_ENV['DB_HOST']       ?? getenv('DB_HOST')       ?: 'localhost');
define('DB_PORT',       $_ENV['DB_PORT']       ?? getenv('DB_PORT')       ?: '1433');
define('DB_DATABASE',   $_ENV['DB_DATABASE']   ?? getenv('DB_DATABASE')   ?: 'Giosoft_LPD');
define('DB_USERNAME',   $_ENV['DB_USERNAME']   ?? getenv('DB_USERNAME')   ?: 'sa');
define('DB_PASSWORD',   $_ENV['DB_PASSWORD']   ?? getenv('DB_PASSWORD')   ?: '#sa.lpd.Canggu.21');
define('SQLITE_PATH',   $_ENV['SQLITE_PATH']   ?? getenv('SQLITE_PATH')   ?: __DIR__ . '/../data/lpd_canggu.sqlite');

define('SALDO_MIN',     50000);
define('MIN_TRANSFER',  10000);
define('MAX_TRANSFER',  5000000);
define('BIAYA_ADMIN_BANK', 5000);

// =====================================================
class DB {
    private static ?PDO $conn    = null;
    private static string $driver = '';

    public static function connect(): PDO {
        if (self::$conn !== null) return self::$conn;

        if (DB_CONNECTION === 'sqlsrv') {
            // --- SQL Server ---
            $dsn = "sqlsrv:Server=" . DB_HOST . "," . DB_PORT
                 . ";Database=" . DB_DATABASE
                 . ";TrustServerCertificate=1;LoginTimeout=5";
            self::$conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT            => 5,
            ]);
            self::$driver = 'sqlsrv';
        } else {
            // --- SQLite ---
            $dir = dirname(SQLITE_PATH);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            self::$conn = new PDO('sqlite:' . SQLITE_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$conn->exec('PRAGMA journal_mode=WAL');
            self::$conn->exec('PRAGMA foreign_keys=ON');
            self::$driver = 'sqlite';
        }

        return self::$conn;
    }

    public static function driver(): string { return self::$driver; }
    public static function isSqlSrv(): bool { return self::$driver === 'sqlsrv'; }

    public static function pdo(): PDO { return self::connect(); }

    public static function all(string $sql, array $p = []): array {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        return $s->fetchAll() ?: [];
    }

    public static function first(string $sql, array $p = []): ?array {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        $r = $s->fetch();
        return $r ?: null;
    }

    public static function run(string $sql, array $p = []): bool {
        return self::connect()->prepare($sql)->execute($p);
    }

    public static function lastId(): string {
        return self::connect()->lastInsertId();
    }

    public static function scalar(string $sql, array $p = []): mixed {
        $s = self::connect()->prepare($sql);
        $s->execute($p);
        $r = $s->fetch(PDO::FETCH_NUM);
        return $r ? $r[0] : null;
    }

    public static function begin(): void  { self::connect()->beginTransaction(); }
    public static function commit(): void { self::connect()->commit(); }
    public static function rollback(): void {
        if (self::connect()->inTransaction()) self::connect()->rollBack();
    }

    // ---- INIT SCHEMA (SQLite) ----
    public static function initSchema(): array {
        $pdo = self::connect();
        $msgs = [];

        // gmob_nasabah - data mobile banking nasabah
        $pdo->exec("CREATE TABLE IF NOT EXISTS gmob_nasabah (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            noid        TEXT UNIQUE NOT NULL,
            nama        TEXT NOT NULL,
            norek       TEXT UNIQUE NOT NULL,
            username    TEXT UNIQUE NOT NULL,
            pass_crypto TEXT NOT NULL,
            pin_crypto  TEXT DEFAULT '123456',
            phone       TEXT DEFAULT '',
            email       TEXT DEFAULT '',
            alamat      TEXT DEFAULT '',
            status      TEXT DEFAULT 'R',
            imei_code   TEXT DEFAULT '',
            aes_key     TEXT DEFAULT '',
            aes_iv      TEXT DEFAULT '',
            aes_cs      TEXT DEFAULT '',
            created_at  TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at  TEXT DEFAULT CURRENT_TIMESTAMP
        )"); $msgs[] = 'gmob_nasabah OK';

        // gmob_rekening - daftar rekening per nasabah
        $pdo->exec("CREATE TABLE IF NOT EXISTS gmob_rekening (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            noid        TEXT NOT NULL,
            notab       TEXT UNIQUE NOT NULL,
            nama        TEXT NOT NULL,
            produk      TEXT DEFAULT 'Tabungan',
            saldo       REAL DEFAULT 0,
            status      TEXT DEFAULT 'A',
            created_at  TEXT DEFAULT CURRENT_TIMESTAMP
        )"); $msgs[] = 'gmob_rekening OK';

        // gtb_nasabah - master data nasabah dari core banking
        $pdo->exec("CREATE TABLE IF NOT EXISTS gtb_nasabah (
            id      INTEGER PRIMARY KEY AUTOINCREMENT,
            linker  TEXT UNIQUE NOT NULL,
            nasabah TEXT NOT NULL,
            status  TEXT DEFAULT 'A'
        )"); $msgs[] = 'gtb_nasabah OK';

        // gtb_folio - buku besar transaksi
        $pdo->exec("CREATE TABLE IF NOT EXISTS gtb_folio (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            linker      TEXT NOT NULL,
            trans_date  TEXT NOT NULL,
            mutasi_date TEXT NOT NULL,
            trans_code  TEXT NOT NULL,
            debit       REAL DEFAULT 0,
            credit      REAL DEFAULT 0,
            remark      TEXT DEFAULT '',
            trans_no    TEXT DEFAULT '',
            bill_no     TEXT DEFAULT '',
            userid      TEXT DEFAULT 'system',
            saldo       REAL DEFAULT 0,
            debit_val   TEXT DEFAULT 'F',
            created_at  TEXT DEFAULT CURRENT_TIMESTAMP
        )"); $msgs[] = 'gtb_folio OK';

        // gmob_transfer - log transfer
        $pdo->exec("CREATE TABLE IF NOT EXISTS gmob_transfer (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            trans_date TEXT NOT NULL,
            jenis      TEXT DEFAULT 'LPD',
            from_norek TEXT NOT NULL,
            from_name  TEXT DEFAULT '',
            to_norek   TEXT NOT NULL,
            to_name    TEXT DEFAULT '',
            bank_code  TEXT DEFAULT '',
            bank_name  TEXT DEFAULT '',
            amount     REAL NOT NULL,
            cost       REAL DEFAULT 0,
            balance    REAL DEFAULT 0,
            remark     TEXT DEFAULT '',
            trans_no   TEXT DEFAULT '',
            ref_no     TEXT DEFAULT '',
            status     TEXT DEFAULT '00',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )"); $msgs[] = 'gmob_transfer OK';

        // gcore_bankcode - master bank
        $pdo->exec("CREATE TABLE IF NOT EXISTS gcore_bankcode (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            bank_code     TEXT UNIQUE NOT NULL,
            bank_name     TEXT NOT NULL,
            transfer_cost REAL DEFAULT 5000,
            revenue       REAL DEFAULT 1500
        )"); $msgs[] = 'gcore_bankcode OK';

        // gmob_token - token sesi mobile
        $pdo->exec("CREATE TABLE IF NOT EXISTS gmob_token (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            account_no TEXT NOT NULL,
            token      TEXT NOT NULL,
            start_time TEXT NOT NULL,
            end_time   TEXT DEFAULT '',
            status     TEXT DEFAULT 'open'
        )"); $msgs[] = 'gmob_token OK';

        // gmob_log_trans - log semua transaksi
        $pdo->exec("CREATE TABLE IF NOT EXISTS gmob_log_trans (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            norek      TEXT NOT NULL,
            jenis      TEXT NOT NULL,
            keterangan TEXT DEFAULT '',
            amount     REAL DEFAULT 0,
            status     TEXT DEFAULT '00',
            ip_addr    TEXT DEFAULT '',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        )"); $msgs[] = 'gmob_log_trans OK';

        // ---- SEED DATA ----
        $pdo->exec("INSERT OR IGNORE INTO gcore_bankcode (bank_code, bank_name, transfer_cost, revenue) VALUES
            ('014','BCA',5000,1500),
            ('008','Mandiri',5000,1500),
            ('009','BNI',5000,1500),
            ('002','BRI',5000,1500),
            ('011','Danamon',6500,2000),
            ('022','CIMB Niaga',6500,2000),
            ('213','BPD Bali',3500,1000),
            ('013','Permata Bank',6500,2000),
            ('016','Maybank',6500,2000),
            ('110','Bank Sinar',3500,1000),
            ('019','Panin Bank',6500,2000),
            ('028','OCBC NISP',6500,2000)");
        $msgs[] = 'Bank codes seeded OK';

        // Seed admin
        $pdo->exec("INSERT OR IGNORE INTO gmob_nasabah
            (noid,nama,norek,username,pass_crypto,pin_crypto,status)
            VALUES ('CG.000000000','Administrator LPD Canggu','00.000000','admin','admin123','123456','A')");
        $pdo->exec("INSERT OR IGNORE INTO gtb_nasabah (linker,nasabah,status)
            VALUES ('00.000000','Administrator LPD Canggu','A')");
        $pdo->exec("INSERT OR IGNORE INTO gmob_rekening (noid,notab,nama,produk,saldo,status)
            VALUES ('CG.000000000','00.000000','Administrator LPD Canggu','Tabungan',0,'A')");
        $msgs[] = 'Admin seeded OK';

        return $msgs;
    }
}
