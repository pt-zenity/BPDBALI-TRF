# 📦 Panduan Instalasi — LPD Canggu Sistem Transaksi

> Sistem transaksi perbankan LPD (Lembaga Perkreditan Desa) Canggu berbasis PHP murni  
> Support database: **SQLite** (default/lokal) dan **SQL Server / MSSQL** (produksi)

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Clone Repository](#2-clone-repository)
3. [Instalasi PHP & Ekstensi](#3-instalasi-php--ekstensi)
4. [Konfigurasi Database](#4-konfigurasi-database)
   - [Mode A: SQLite (Lokal/Dev)](#mode-a-sqlite-lokaldev)
   - [Mode B: SQL Server (Produksi)](#mode-b-sql-server-produksi)
5. [Menjalankan Aplikasi](#5-menjalankan-aplikasi)
   - [Cara 1: PHP Built-in Server (Sederhana)](#cara-1-php-built-in-server-sederhana)
   - [Cara 2: Dengan PM2 (Daemon)](#cara-2-dengan-pm2-daemon)
   - [Cara 3: Nginx + PHP-FPM (Produksi)](#cara-3-nginx--php-fpm-produksi)
6. [Inisialisasi Database](#6-inisialisasi-database)
7. [Verifikasi Instalasi](#7-verifikasi-instalasi)
8. [Struktur Direktori](#8-struktur-direktori)
9. [Daftar API Endpoint](#9-daftar-api-endpoint)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Persyaratan Sistem

### Minimum
| Komponen | Versi Minimum | Keterangan |
|----------|--------------|------------|
| PHP | 8.1+ | Direkomendasikan 8.4 |
| OS | Ubuntu 20.04 / Debian 11 / Windows Server 2019 | |
| RAM | 512 MB | Minimum untuk SQLite |
| Disk | 500 MB | Untuk aplikasi + data |

### Ekstensi PHP yang Dibutuhkan
| Ekstensi | Fungsi | Wajib? |
|----------|--------|--------|
| `pdo` | Database abstraction layer | ✅ Wajib |
| `pdo_sqlite` | Driver SQLite | ✅ Wajib (mode SQLite) |
| `sqlite3` | SQLite native | ✅ Wajib (mode SQLite) |
| `pdo_sqlsrv` | Driver SQL Server via PDO | ⚠️ Produksi saja |
| `sqlsrv` | Driver SQL Server native | ⚠️ Produksi saja |
| `json` | Parsing JSON | ✅ Wajib |
| `curl` | HTTP request | ✅ Wajib |
| `mbstring` | Multi-byte string | ✅ Wajib |
| `openssl` | Enkripsi | ✅ Wajib |

---

## 2. Clone Repository

```bash
# Clone dari GitHub
git clone https://github.com/pt-zenity/BPDBALI-TRF.git lpd-canggu

# Masuk ke direktori project
cd lpd-canggu

# Buat folder yang diperlukan
mkdir -p data logs public
chmod 755 data logs
```

---

## 3. Instalasi PHP & Ekstensi

### Ubuntu / Debian

```bash
# Update package list
sudo apt-get update

# Install PHP 8.4 dan ekstensi dasar
sudo apt-get install -y \
    php8.4-cli \
    php8.4-sqlite3 \
    php8.4-curl \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-json

# Verifikasi instalasi
php --version
php -m | grep -E "sqlite|pdo|curl|json|mbstring"
```

Contoh output yang diharapkan:
```
PHP 8.4.x (cli)
curl
json
mbstring
pdo_sqlite
sqlite3
```

### Untuk SQL Server (Produksi) — Tambahan

```bash
# 1. Tambah Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list \
    | sudo tee /etc/apt/sources.list.d/mssql-release.list

# 2. Install ODBC Driver for SQL Server
sudo apt-get update
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev

# 3. Install ekstensi PHP sqlsrv via PECL
sudo apt-get install -y php8.4-dev
sudo pecl install sqlsrv pdo_sqlsrv

# 4. Aktifkan ekstensi
echo "extension=sqlsrv.so"     | sudo tee /etc/php/8.4/cli/conf.d/30-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.4/cli/conf.d/30-pdo_sqlsrv.ini

# 5. Verifikasi
php -m | grep sqlsrv
```

### Windows (XAMPP / Laragon / Manual)

```powershell
# Download PHP 8.4 dari https://windows.php.net/download/
# Ekstrak ke C:\php

# Aktifkan ekstensi di php.ini (C:\php\php.ini)
# Hapus tanda ; di depan baris berikut:
; extension=pdo_sqlite
; extension=sqlite3
; extension=curl
; extension=mbstring
; extension=openssl

# Untuk SQL Server — download driver dari:
# https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
# Copy php_sqlsrv_84_ts.dll dan php_pdo_sqlsrv_84_ts.dll ke C:\php\ext\
# Tambahkan di php.ini:
; extension=sqlsrv
; extension=pdo_sqlsrv

# Verifikasi
php -m
```

---

## 4. Konfigurasi Database

Buat file `.env` di root direktori project:

```bash
cp .env.example .env   # Jika ada
# atau buat manual:
nano .env
```

### Mode A: SQLite (Lokal/Dev)

```ini
# .env — Mode SQLite
DB_CONNECTION=sqlite
SQLITE_PATH=/path/ke/lpd-canggu/data/lpd_canggu.sqlite

# Batasan transaksi
SALDO_MIN=50000
MIN_TRANSFER=10000
MAX_TRANSFER=5000000
```

> **SQLite adalah default** — jika `.env` tidak ada, aplikasi otomatis menggunakan SQLite
> dengan path `data/lpd_canggu.sqlite` di dalam folder project.

### Mode B: SQL Server (Produksi)

```ini
# .env — Mode SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=192.168.1.100        # IP server SQL Server
DB_PORT=1433                  # Port default SQL Server
DB_DATABASE=Giosoft_LPD      # Nama database
DB_USERNAME=sa                # Username
DB_PASSWORD=#sa.lpd.Canggu.21 # Password

# Batasan transaksi
SALDO_MIN=50000
MIN_TRANSFER=10000
MAX_TRANSFER=5000000
```

> **Penting:** Pastikan SQL Server mengizinkan koneksi TCP/IP di port 1433
> dan firewall sudah dibuka.

---

## 5. Menjalankan Aplikasi

### Cara 1: PHP Built-in Server (Sederhana)

Cocok untuk **development** atau **testing** lokal.

```bash
# Masuk ke folder project
cd /path/ke/lpd-canggu

# Jalankan server di port 8080
php -S 0.0.0.0:8080 router.php

# Akses di browser:
# http://localhost:8080
```

Hentikan dengan `Ctrl + C`.

---

### Cara 2: Dengan PM2 (Daemon)

Cocok untuk **server** agar proses tetap berjalan di background.

```bash
# Install Node.js & PM2 (jika belum ada)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt-get install -y nodejs
sudo npm install -g pm2

# Sesuaikan path di ecosystem.config.cjs jika perlu
nano ecosystem.config.cjs
```

Isi `ecosystem.config.cjs`:

```javascript
module.exports = {
  apps: [
    {
      name: 'lpd-php',
      script: '/usr/bin/php',            // Sesuaikan path PHP: `which php`
      args: '-S 0.0.0.0:8080 /FULL/PATH/lpd-canggu/router.php',
      cwd: '/FULL/PATH/lpd-canggu',      // Ganti dengan path aktual
      interpreter: 'none',
      watch: false,
      instances: 1,
      exec_mode: 'fork',
      env: {
        DB_CONNECTION: 'sqlite',
        SQLITE_PATH: '/FULL/PATH/lpd-canggu/data/lpd_canggu.sqlite',
      },
      error_file: '/FULL/PATH/lpd-canggu/logs/pm2-error.log',
      out_file:   '/FULL/PATH/lpd-canggu/logs/pm2-out.log',
    }
  ]
};
```

```bash
# Jalankan dengan PM2
pm2 start ecosystem.config.cjs

# Cek status
pm2 list
pm2 logs lpd-php --nostream

# Auto-start saat reboot
pm2 startup
pm2 save
```

Perintah PM2 berguna:
```bash
pm2 restart lpd-php   # Restart aplikasi
pm2 stop lpd-php      # Hentikan
pm2 delete lpd-php    # Hapus dari PM2
pm2 monit             # Monitor real-time
```

---

### Cara 3: Nginx + PHP-FPM (Produksi)

Cocok untuk **production server** dengan performa tinggi.

#### Install PHP-FPM

```bash
sudo apt-get install -y php8.4-fpm

# Verifikasi
systemctl status php8.4-fpm
```

#### Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/lpd-canggu
```

```nginx
server {
    listen 80;
    server_name lpd.contoh.id;          # Ganti dengan domain/IP Anda
    root /var/www/lpd-canggu;           # Path ke folder project
    index index.php;

    # Semua request ke router.php kecuali file statis
    location / {
        try_files $uri $uri/ /router.php?$query_string;
    }

    # Static files (CSS, JS, gambar)
    location ~* \.(css|js|png|jpg|jpeg|ico|svg|woff2?)$ {
        expires 7d;
        access_log off;
    }

    # PHP-FPM handler
    location ~ \.php$ {
        fastcgi_pass   unix:/run/php/php8.4-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_param  PATH_INFO $fastcgi_path_info;
        fastcgi_read_timeout 60;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    # Sembunyikan file sensitif
    location ~ /\.(env|git|htaccess) {
        deny all;
    }

    # Log
    access_log /var/log/nginx/lpd-canggu-access.log;
    error_log  /var/log/nginx/lpd-canggu-error.log;
}
```

```bash
# Aktifkan site
sudo ln -s /etc/nginx/sites-available/lpd-canggu /etc/nginx/sites-enabled/
sudo nginx -t          # Test konfigurasi
sudo systemctl reload nginx

# Pastikan folder bisa ditulis oleh www-data
sudo chown -R www-data:www-data /var/www/lpd-canggu/data
sudo chown -R www-data:www-data /var/www/lpd-canggu/logs
sudo chmod 755 /var/www/lpd-canggu/data
sudo chmod 755 /var/www/lpd-canggu/logs
```

#### Akses via domain

```
http://lpd.contoh.id
```

---

## 6. Inisialisasi Database

Setelah server berjalan, **wajib** menginisialisasi database terlebih dahulu.

### Via Browser

1. Buka aplikasi di browser
2. Klik tombol **"Init DB"** di pojok kanan atas topbar
3. Akan muncul notifikasi sukses

### Via API (curl)

```bash
curl -X POST http://localhost:8080/api/init-db
```

Respons sukses:
```json
{
  "status": "00",
  "message": "Database berhasil diinisialisasi",
  "driver": "sqlite",
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

> **Catatan:** Init DB aman dijalankan berulang kali — menggunakan `INSERT OR IGNORE`
> sehingga data yang sudah ada tidak akan terhapus.

---

## 7. Verifikasi Instalasi

Jalankan perintah berikut untuk memastikan semua berfungsi:

```bash
BASE="http://localhost:8080"

# 1. Cek halaman utama
echo "=== Halaman Utama ===" 
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" $BASE/

# 2. Init Database
echo "=== Init Database ==="
curl -s -X POST $BASE/api/init-db | python3 -m json.tool

# 3. Buat nasabah baru
echo "=== Buat Nasabah ==="
curl -s -X POST $BASE/api/nasabah \
  -H "Content-Type: application/json" \
  -d '{"nama":"I Wayan Test","username":"wayan.test","pass_crypto":"test123","phone":"081234567890"}' \
  | python3 -m json.tool

# 4. Aktifkan nasabah (ganti {ID} dengan ID dari response buat nasabah)
curl -s -X PUT $BASE/api/nasabah/2/status \
  -H "Content-Type: application/json" \
  -d '{"status":"A"}' | python3 -m json.tool

# 5. Setor tunai
curl -s -X POST $BASE/api/setor \
  -H "Content-Type: application/json" \
  -d '{"norek":"01.000002","amount":500000,"remark":"Setoran awal"}' \
  | python3 -m json.tool

# 6. Cek saldo
curl -s $BASE/api/saldo/01.000002 | python3 -m json.tool

# 7. Dashboard
curl -s $BASE/api/dashboard | python3 -m json.tool
```

---

## 8. Struktur Direktori

```
lpd-canggu/
│
├── router.php                  ← Entry point & router utama
├── index.php                   ← Halaman SPA (HTML + CSS + JS)
├── .env                        ← Konfigurasi environment (buat manual)
├── .env.example                ← Contoh konfigurasi
├── .gitignore
├── ecosystem.config.cjs        ← Konfigurasi PM2
├── INSTALL.md                  ← Dokumentasi ini
│
├── config/
│   └── database.php            ← Kelas DB, konstanta, inisialisasi schema
│
├── includes/
│   ├── bootstrap.php           ← Auto-load semua dependencies
│   └── helpers.php             ← Helper functions (json_ok, rp, trans_no, dll)
│
├── api/                        ← API Endpoints
│   ├── init.php                ← POST /api/init-db
│   ├── nasabah.php             ← CRUD /api/nasabah
│   ├── rekening.php            ← GET  /api/rekening/{norek}
│   ├── saldo.php               ← GET  /api/saldo/{norek}
│   ├── setor.php               ← POST /api/setor
│   ├── tarik.php               ← POST /api/tarik
│   ├── transfer_lpd.php        ← POST /api/transfer-lpd
│   ├── transfer_bank.php       ← POST /api/transfer-bank
│   ├── mutasi.php              ← GET  /api/mutasi/{norek}
│   ├── riwayat_transfer.php    ← GET  /api/riwayat-transfer/{norek}
│   ├── dashboard.php           ← GET  /api/dashboard
│   ├── bank_list.php           ← GET  /api/bank-list
│   └── login.php               ← POST /api/login
│
├── public/                     ← Static assets (CSS, JS, gambar)
│
├── data/                       ← Database SQLite (auto-created, jangan di-commit)
│   └── lpd_canggu.sqlite
│
└── logs/                       ← Log aplikasi (auto-created)
    ├── php_error.log
    ├── pm2-out.log
    └── pm2-error.log
```

---

## 9. Daftar API Endpoint

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/api/init-db` | Inisialisasi / reset schema database |
| `GET` | `/api/nasabah` | List semua nasabah (`?q=cari&status=A`) |
| `GET` | `/api/nasabah/{id}` | Detail satu nasabah |
| `POST` | `/api/nasabah` | Tambah nasabah baru |
| `PUT` | `/api/nasabah/{id}` | Update data nasabah |
| `PUT` | `/api/nasabah/{id}/status` | Ubah status (`A`/`R`/`B`/`T`) |
| `DELETE` | `/api/nasabah/{id}` | Hapus nasabah (saldo harus 0) |
| `GET` | `/api/rekening/{norek}` | Info rekening |
| `GET` | `/api/saldo/{norek}` | Cek saldo rekening |
| `GET` | `/api/mutasi/{norek}` | Mutasi rekening (`?start_date=&end_date=&limit=`) |
| `POST` | `/api/setor` | Setor tunai |
| `POST` | `/api/tarik` | Tarik tunai |
| `POST` | `/api/transfer-lpd` | Transfer antar rekening LPD |
| `POST` | `/api/transfer-bank` | Transfer ke bank lain (simulasi) |
| `GET` | `/api/riwayat-transfer/{norek}` | Riwayat transfer (`?jenis=LPD\|BANK`) |
| `GET` | `/api/bank-list` | Daftar bank yang tersedia |
| `GET` | `/api/dashboard` | Statistik & transaksi terbaru |
| `POST` | `/api/login` | Login nasabah |

### Contoh Request Body

**POST /api/nasabah** — Tambah Nasabah
```json
{
  "nama": "I Wayan Sudarsana",
  "username": "wayan.sudarsana",
  "pass_crypto": "password123",
  "pin_crypto": "112233",
  "phone": "081234567890",
  "email": "wayan@email.com",
  "alamat": "Br. Canggu, Kuta Utara, Badung"
}
```

**POST /api/setor** — Setor Tunai
```json
{
  "norek": "01.000002",
  "amount": 1000000,
  "remark": "Setor tunai"
}
```

**POST /api/tarik** — Tarik Tunai
```json
{
  "norek": "01.000002",
  "amount": 500000,
  "remark": "Tarik tunai"
}
```

**POST /api/transfer-lpd** — Transfer Antar LPD
```json
{
  "from_norek": "01.000002",
  "to_norek": "01.000003",
  "amount": 200000,
  "remark": "Bayar cicilan"
}
```

**POST /api/transfer-bank** — Transfer ke Bank Lain
```json
{
  "from_norek": "01.000002",
  "bank_code": "014",
  "bank_acc": "1234567890",
  "to_name": "Nama Penerima",
  "amount": 500000,
  "remark": "Pembayaran"
}
```

### Kode Status Response

| Status | Keterangan |
|--------|------------|
| `00` | Sukses |
| `01` | Data tidak lengkap / input tidak valid |
| `02` | Data sudah ada / duplikat |
| `03` | Username / password salah |
| `04` | Data tidak ditemukan |
| `05` | Rekening / nasabah tidak aktif |
| `06` | Saldo tidak mencukupi |
| `07` | Akun diblokir / ditutup |
| `51` | Saldo tidak cukup |
| `99` | Server error |

---

## 10. Troubleshooting

### ❌ Error: `pdo_sqlite` not found
```bash
# Ubuntu/Debian
sudo apt-get install php8.4-sqlite3

# Verifikasi
php -m | grep sqlite
```

### ❌ Error: Cannot connect to SQL Server (HYT00 / Login Timeout)
Kemungkinan penyebab:
1. SQL Server tidak berjalan → Cek `services.msc` (Windows) atau `systemctl status mssql-server`
2. Port 1433 diblokir firewall → Buka port: `sudo ufw allow 1433`
3. TCP/IP tidak aktif → Buka **SQL Server Configuration Manager** → Enable TCP/IP
4. `DB_HOST` salah → Pastikan IP/hostname dapat di-ping dari server PHP

```bash
# Test koneksi manual
nc -zv <DB_HOST> 1433
# atau
telnet <DB_HOST> 1433
```

### ❌ Error: `Permission denied` pada folder data/ atau logs/
```bash
# Linux
chmod 755 data/ logs/
chown -R www-data:www-data data/ logs/   # Jika menggunakan Nginx

# Atau jika dijalankan sebagai user biasa
chmod 777 data/ logs/
```

### ❌ Error: Port 8080 sudah digunakan
```bash
# Cari proses yang menggunakan port 8080
fuser -k 8080/tcp
# atau
kill $(lsof -t -i:8080)

# Ganti ke port lain
php -S 0.0.0.0:9090 router.php
```

### ❌ Halaman kosong / 500 Internal Server Error
```bash
# Aktifkan error display sementara untuk debug
php -d display_errors=1 -S 0.0.0.0:8080 router.php

# Cek log error
cat logs/php_error.log
cat logs/pm2-error.log
```

### ❌ API return 404 "Endpoint tidak ditemukan"
Pastikan request method sesuai:
- `GET` untuk endpoint yang mengambil data
- `POST` untuk transaksi & tambah data
- `PUT` untuk update data
- `DELETE` untuk hapus

```bash
# Contoh benar:
curl -X POST http://localhost:8080/api/setor ...   # ✅
curl http://localhost:8080/api/setor ...           # ❌ (GET tidak terdaftar)
```

### ❌ Data tidak tersimpan setelah restart (SQLite)
Pastikan path `SQLITE_PATH` menggunakan **path absolut**:

```ini
# .env
SQLITE_PATH=/home/user/lpd-canggu/data/lpd_canggu.sqlite  # ✅ Absolut
# SQLITE_PATH=data/lpd_canggu.sqlite                       # ❌ Relatif (bisa berubah)
```

---

## 🔒 Catatan Keamanan untuk Produksi

1. **Ganti password default** `admin123` setelah instalasi pertama
2. **Jangan expose port PHP** ke internet langsung — gunakan Nginx sebagai reverse proxy
3. **Batasi akses** folder `data/` dan `logs/` dari web dengan Nginx:
   ```nginx
   location ~ ^/(data|logs)/ { deny all; }
   ```
4. **Gunakan HTTPS** — pasang SSL certificate (Let's Encrypt gratis):
   ```bash
   sudo apt-get install certbot python3-certbot-nginx
   sudo certbot --nginx -d lpd.contoh.id
   ```
5. **Backup rutin** file SQLite:
   ```bash
   # Tambahkan ke cron
   0 2 * * * cp /path/ke/data/lpd_canggu.sqlite /backup/lpd_$(date +\%Y\%m\%d).sqlite
   ```

---

## 📞 Informasi Repository

| Item | Detail |
|------|--------|
| Repository | https://github.com/pt-zenity/BPDBALI-TRF |
| Branch | `main` |
| Bahasa | PHP 8.4 |
| Database | SQLite / SQL Server |
| Lisensi | Proprietary |
