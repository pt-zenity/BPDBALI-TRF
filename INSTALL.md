# LPD Canggu – Panduan Instalasi

**Commit:** `2e7b490` | **Branch:** `main`  
**Repo:** https://github.com/pt-zenity/BPDBALI-TRF  
**Stack:** Pure PHP (no framework, no Composer) · SQLite (default) / SQL Server

---

## Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Clone Repositori](#2-clone-repositori)
3. [Instalasi PHP & Ekstensi](#3-instalasi-php--ekstensi)
4. [Konfigurasi Database](#4-konfigurasi-database)
5. [Cara Menjalankan](#5-cara-menjalankan)
   - [Apache + mod_php (disarankan)](#a-apache--mod_php-disarankan)
   - [PHP Built-in Server](#b-php-built-in-server)
   - [PM2 Daemon](#c-pm2-daemon)
6. [Inisialisasi Database](#6-inisialisasi-database)
7. [Verifikasi Instalasi](#7-verifikasi-instalasi)
8. [Struktur Direktori](#8-struktur-direktori)
9. [Daftar Endpoint API](#9-daftar-endpoint-api)
10. [Kode Status Respons](#10-kode-status-respons)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. Persyaratan Sistem

| Komponen | Versi | Catatan |
|----------|-------|---------|
| PHP | 7.0 – 8.4 | Disarankan 8.1+ |
| Ekstensi PHP | pdo, pdo_sqlite, sqlite3, json, mbstring | Wajib untuk SQLite |
| Ekstensi PHP | pdo_sqlsrv, sqlsrv | Wajib untuk SQL Server |
| Web Server | Apache 2.4+ / PHP Built-in / PM2 | Pilih salah satu |
| OS | Ubuntu/Debian, CentOS/RHEL, Windows | Cross-platform |

---

## 2. Clone Repositori

```bash
git clone https://github.com/pt-zenity/BPDBALI-TRF.git lpd-canggu
cd lpd-canggu

# Buat direktori yang diperlukan jika belum ada
mkdir -p data logs public/css public/js
chmod 775 data logs
```

---

## 3. Instalasi PHP & Ekstensi

### Ubuntu / Debian

```bash
# PHP 8.4
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml

# Apache + mod_php
sudo apt install -y apache2 libapache2-mod-php8.4

# Aktifkan modul Apache
sudo a2enmod rewrite headers expires deflate
sudo systemctl restart apache2
```

### CentOS / RHEL / AlmaLinux

```bash
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
sudo dnf module enable php:remi-8.4 -y
sudo dnf install -y php php-cli php-pdo php-sqlite3 php-mbstring httpd
sudo systemctl enable --now httpd
```

### Windows (XAMPP / Laragon)

- **XAMPP:** Download dari https://apachefriends.org — SQLite sudah aktif secara default.
- **Laragon:** Download dari https://laragon.org — siap pakai tanpa konfigurasi tambahan.
- Letakkan folder proyek di `C:\xampp\htdocs\lpd-canggu\` atau `C:\laragon\www\lpd-canggu\`.

### Ekstensi SQL Server (opsional, hanya jika pakai SQL Server)

```bash
# Ubuntu – ODBC Driver
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/$(lsb_release -rs)/prod.list \
  | sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt update && sudo ACCEPT_EULA=Y apt install -y msodbcsql18 unixodbc-dev

# PHP sqlsrv extension
sudo pecl install sqlsrv pdo_sqlsrv
echo "extension=sqlsrv.so"     | sudo tee /etc/php/8.4/mods-available/sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.4/mods-available/pdo_sqlsrv.ini
sudo phpenmod sqlsrv pdo_sqlsrv && sudo systemctl restart apache2
```

---

## 4. Konfigurasi Database

Salin `.env.example` ke `.env` dan sesuaikan:

```bash
cp .env.example .env
nano .env
```

### Mode SQLite (default)

```env
DB_CONNECTION=sqlite
SQLITE_PATH=/var/www/html/lpd-canggu/data/lpd_canggu.sqlite
```

### Mode SQL Server

```env
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=Giosoft_LPD
DB_USERNAME=sa
DB_PASSWORD=PasswordAnda
```

### Batas Transaksi (opsional, sudah ada default)

```env
SALDO_MIN=50000       # Saldo minimum mengendap
MIN_TRANSFER=10000    # Minimal nominal transfer
MAX_TRANSFER=5000000  # Maksimal nominal transfer
```

---

## 5. Cara Menjalankan

### a. Apache + mod_php (disarankan)

File `.htaccess` sudah disertakan di repositori dan mengatur routing otomatis.

```bash
# Salin ke document root Apache
sudo cp -r /path/ke/lpd-canggu /var/www/html/lpd-canggu
sudo chown -R www-data:www-data /var/www/html/lpd-canggu
sudo chmod -R 755 /var/www/html/lpd-canggu
sudo chmod -R 775 /var/www/html/lpd-canggu/data /var/www/html/lpd-canggu/logs
```

Buat Virtual Host:

```apache
# /etc/apache2/sites-available/lpd-canggu.conf
<VirtualHost *:80>
    ServerName lpd.example.com
    DocumentRoot /var/www/html/lpd-canggu

    <Directory /var/www/html/lpd-canggu>
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/lpd-canggu-error.log
    CustomLog ${APACHE_LOG_DIR}/lpd-canggu-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite lpd-canggu.conf
sudo systemctl reload apache2
```

Akses: `http://lpd.example.com/`  
API: `http://lpd.example.com/api/dashboard`

### b. PHP Built-in Server

Cocok untuk development / testing lokal. Tidak perlu Apache/Nginx.

```bash
cd /path/ke/lpd-canggu
php -S 0.0.0.0:8080 router.php
```

Akses: `http://localhost:8080/`  
API: `http://localhost:8080/api/dashboard`

### c. PM2 Daemon

Cocok untuk server Linux tanpa web server. File `ecosystem.config.cjs` sudah tersedia di repositori.

```bash
# Install PM2 (jika belum)
npm install -g pm2

# Jalankan
cd /path/ke/lpd-canggu
pm2 start ecosystem.config.cjs
pm2 save
pm2 startup   # agar auto-start saat reboot

# Perintah PM2 berguna
pm2 status
pm2 logs lpd-php --nostream
pm2 restart lpd-php
pm2 stop lpd-php
```

---

## 6. Inisialisasi Database

Setelah server berjalan, jalankan sekali untuk membuat semua tabel dan data awal:

```bash
curl -s -X POST http://localhost:8080/api/init-db | python3 -m json.tool
```

Respons sukses:

```json
{
  "status": "00",
  "message": "Database berhasil diinisialisasi",
  "driver": "sqlite",
  "path": "/path/ke/data/lpd_canggu.sqlite",
  "detail": [
    "gmob_nasabah OK",
    "gmob_rekening OK",
    "gtb_nasabah OK",
    "gtb_folio OK",
    "gmob_transfer OK",
    "gcore_bankcode OK",
    "gmob_token OK",
    "gmob_log_trans OK",
    "Bank codes seeded OK",
    "Admin seeded OK"
  ]
}
```

**Akun admin default:**  
Username: `admin` | Password: `admin123`  
> ⚠️ Segera ganti password admin setelah instalasi pertama.

---

## 7. Verifikasi Instalasi

Jalankan skrip berikut untuk memastikan semua endpoint berfungsi:

```bash
BASE="http://localhost:8080"

echo "=== 1. Halaman utama ==="
curl -s -o /dev/null -w "HTTP %{http_code}\n" $BASE/

echo "=== 2. Login admin ==="
curl -s -X POST $BASE/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' | python3 -c \
  "import sys,json; d=json.load(sys.stdin); print('Status:', d['status'], '|', d.get('message',''))"

echo "=== 3. Dashboard ==="
curl -s $BASE/api/dashboard | python3 -c \
  "import sys,json; d=json.load(sys.stdin); s=d.get('stats',{}); \
   print('Nasabah:', s.get('total_nasabah'), '| Saldo:', s.get('total_saldo_fmt'))"

echo "=== 4. Bank list ==="
curl -s $BASE/api/bank-list | python3 -c \
  "import sys,json; d=json.load(sys.stdin); print('Bank tersedia:', len(d.get('data',[])))"

echo "=== 5. Buat nasabah ==="
curl -s -X POST $BASE/api/nasabah \
  -H "Content-Type: application/json" \
  -d '{"nama":"Test Nasabah","username":"test01","pass_crypto":"test123"}' | python3 -c \
  "import sys,json; d=json.load(sys.stdin); print('Status:', d['status'], '| Norek:', d.get('norek',''))"
```

---

## 8. Struktur Direktori

```
lpd-canggu/
├── router.php              # Router utama (PHP built-in server)
├── index.php               # SPA frontend (HTML/CSS/JS)
├── .htaccess               # Routing Apache + keamanan header
├── .user.ini               # Konfigurasi PHP-FPM
├── .env.example            # Template konfigurasi environment
├── .env                    # Konfigurasi aktif (buat dari .env.example)
├── ecosystem.config.cjs    # Konfigurasi PM2
│
├── config/
│   └── database.php        # Kelas DB, koneksi SQLite/SQL Server, konstanta
│
├── includes/
│   ├── bootstrap.php       # Load config + helper + init CORS + koneksi DB
│   └── helpers.php         # Fungsi utilitas (json_ok, rp, insert_folio, dll)
│
├── api/                    # Endpoint API (13 file)
│   ├── init.php            # POST /api/init-db
│   ├── login.php           # POST /api/login
│   ├── nasabah.php         # CRUD /api/nasabah
│   ├── rekening.php        # GET  /api/rekening/{norek}
│   ├── saldo.php           # GET  /api/saldo/{norek}
│   ├── mutasi.php          # GET  /api/mutasi/{norek}
│   ├── setor.php           # POST /api/setor
│   ├── tarik.php           # POST /api/tarik
│   ├── transfer_lpd.php    # POST /api/transfer-lpd
│   ├── transfer_bank.php   # POST /api/transfer-bank
│   ├── riwayat_transfer.php# GET  /api/riwayat-transfer/{norek}
│   ├── bank_list.php       # GET  /api/bank-list
│   └── dashboard.php       # GET  /api/dashboard
│
├── data/
│   └── lpd_canggu.sqlite   # File database SQLite
│
├── logs/
│   ├── php_error.log       # Log error PHP
│   └── pm2-*.log           # Log PM2 (jika pakai PM2)
│
└── public/
    ├── css/                # Asset CSS statis
    └── js/                 # Asset JS statis
```

---

## 9. Daftar Endpoint API

Semua respons dalam format JSON. Field `status: "00"` = sukses.

### Autentikasi

| Method | Endpoint | Deskripsi | Body |
|--------|----------|-----------|------|
| POST | `/api/login` | Login nasabah / admin | `{"username":"...", "password":"..."}` |

### Nasabah

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/nasabah` | Daftar nasabah (filter: `?q=nama&status=A`) |
| GET | `/api/nasabah/{id}` | Detail nasabah + histori |
| POST | `/api/nasabah` | Tambah nasabah baru |
| PUT | `/api/nasabah/{id}` | Update data nasabah |
| PUT | `/api/nasabah/{id}/status` | Ubah status (`A/R/B/T`) |
| DELETE | `/api/nasabah/{id}` | Hapus nasabah (saldo harus 0) |

### Rekening & Saldo

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/rekening/{norek}` | Info rekening lengkap |
| GET | `/api/saldo/{norek}` | Cek saldo rekening |
| GET | `/api/mutasi/{norek}` | Riwayat mutasi (filter: `?limit=50&start_date=&end_date=&jenis=debit/kredit`) |

### Transaksi

| Method | Endpoint | Deskripsi | Body |
|--------|----------|-----------|------|
| POST | `/api/setor` | Setoran tunai | `{"norek":"01.000002","amount":100000}` |
| POST | `/api/tarik` | Penarikan tunai | `{"norek":"01.000002","amount":50000}` |
| POST | `/api/transfer-lpd` | Transfer antar rekening LPD | `{"from_norek":"01.000002","to_norek":"01.000003","amount":100000}` |
| POST | `/api/transfer-bank` | Transfer ke bank lain | `{"from_norek":"01.000002","bank_code":"014","bank_acc":"1234567890","amount":100000}` |
| GET | `/api/riwayat-transfer/{norek}` | Riwayat transfer (filter: `?jenis=LPD/BANK`) |

### Referensi & Laporan

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/bank-list` | Daftar 12 bank beserta biaya transfer |
| GET | `/api/dashboard` | Statistik sistem (nasabah, saldo, transaksi hari ini) |
| POST | `/api/init-db` | Inisialisasi / reset schema database |

### Daftar Kode Bank

| Kode | Nama Bank | Biaya Transfer |
|------|-----------|----------------|
| 002 | BRI | Rp 5.000 |
| 008 | Mandiri | Rp 5.000 |
| 009 | BNI | Rp 5.000 |
| 011 | Danamon | Rp 6.500 |
| 013 | Permata Bank | Rp 6.500 |
| 014 | BCA | Rp 5.000 |
| 016 | Maybank | Rp 6.500 |
| 019 | Panin Bank | Rp 6.500 |
| 022 | CIMB Niaga | Rp 6.500 |
| 028 | OCBC NISP | Rp 6.500 |
| 110 | Bank Sinar | Rp 3.500 |
| 213 | BPD Bali | Rp 3.500 |

---

## 10. Kode Status Respons

| Kode | Arti |
|------|------|
| `00` | Sukses |
| `01` | Parameter tidak valid / tidak lengkap |
| `02` | Data sudah ada (duplikat) |
| `03` | Autentikasi gagal (username/password salah) |
| `04` | Data tidak ditemukan |
| `05` | Rekening atau nasabah tidak aktif |
| `06` | Tidak bisa hapus (saldo masih ada) |
| `07` | Akun diblokir atau sudah ditutup |
| `51` | Saldo tidak cukup |
| `99` | Server error |
| `404` | Endpoint tidak ditemukan |
| `405` | HTTP method tidak diizinkan |

---

## 11. Troubleshooting

### `php: command not found`
```bash
sudo apt install php8.4-cli      # Ubuntu/Debian
sudo dnf install php-cli         # CentOS/RHEL
# Windows: tambahkan PHP ke PATH di System Environment Variables
```

### `could not find driver` (SQLite)
```bash
sudo apt install php8.4-sqlite3
# Verifikasi:
php -m | grep -i sqlite
```

### `Koneksi database gagal` (SQL Server)
```bash
# Cek ekstensi
php -m | grep sqlsrv
# Test koneksi manual
sqlcmd -S localhost,1433 -U sa -P PasswordAnda -Q "SELECT @@VERSION"
```

### Permission denied pada `data/` atau `logs/`
```bash
sudo chown -R www-data:www-data data/ logs/
sudo chmod -R 775 data/ logs/
```

### Port 8080 sudah dipakai
```bash
fuser -k 8080/tcp
php -S 0.0.0.0:8080 router.php
```

### `.htaccess` tidak berjalan di Apache
```bash
# Pastikan AllowOverride All aktif
sudo a2enmod rewrite
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### Halaman kosong / 500 Error
```bash
# Cek log error
tail -f logs/php_error.log
tail -f /var/log/apache2/error.log

# Pastikan display_errors aktif saat debug
php -S 0.0.0.0:8080 router.php 2>&1 | tail -20
```

### API mengembalikan HTML bukan JSON
- Pastikan request menggunakan header `Content-Type: application/json`
- Pastikan endpoint diakses dengan URL yang benar (awali dengan `/api/`)

---

## Catatan Keamanan

- Ganti password admin default `admin123` setelah instalasi pertama melalui endpoint `PUT /api/nasabah/{id}`.
- Nonaktifkan endpoint `/api/init-db` di produksi (blokir di `.htaccess` atau hapus file `api/init.php`).
- Selalu gunakan HTTPS di produksi — konfigurasi SSL dengan Certbot: `sudo certbot --apache -d domain.com`.
- Pastikan folder `data/` dan `logs/` tidak dapat diakses langsung dari browser (sudah diatur di `.htaccess`).
- Backup SQLite secara rutin: `cp data/lpd_canggu.sqlite data/backup_$(date +%Y%m%d).sqlite`
