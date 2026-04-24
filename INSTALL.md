# 📦 Panduan Instalasi — LPD Canggu Sistem Transaksi

> Sistem transaksi perbankan LPD (Lembaga Perkreditan Desa) Canggu berbasis PHP murni  
> Support database: **SQLite** (default/lokal) dan **SQL Server / MSSQL** (produksi)  
> Kompatibel: **PHP 7.0 — PHP 8.4** (tanpa framework, tanpa Composer)

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Clone Repository](#2-clone-repository)
3. [Instalasi PHP & Ekstensi](#3-instalasi-php--ekstensi)
   - [Ubuntu / Debian (PHP 7.x)](#ubuntudebian--php-7x)
   - [Ubuntu / Debian (PHP 8.x)](#ubuntudebian--php-8x)
   - [CentOS / RHEL / AlmaLinux](#centos--rhel--almalinux)
   - [Windows (XAMPP / Laragon)](#windows-xampp--laragon)
   - [Tambahan: SQL Server (Produksi)](#tambahan-sql-server-produksi)
4. [Konfigurasi Database](#4-konfigurasi-database)
   - [Mode A: SQLite (Lokal/Dev)](#mode-a-sqlite-lokaldev)
   - [Mode B: SQL Server (Produksi)](#mode-b-sql-server-produksi)
5. [Menjalankan Aplikasi](#5-menjalankan-aplikasi)
   - [Cara 1: PHP Built-in Server (Sederhana)](#cara-1-php-built-in-server-sederhana)
   - [Cara 2: Dengan PM2 (Daemon)](#cara-2-dengan-pm2-daemon)
   - [Cara 3: Nginx + PHP-FPM (Produksi)](#cara-3-nginx--php-fpm-produksi)
   - [Cara 4: Apache + mod_php](#cara-4-apache--mod_php)
6. [Inisialisasi Database](#6-inisialisasi-database)
7. [Verifikasi Instalasi](#7-verifikasi-instalasi)
8. [Struktur Direktori](#8-struktur-direktori)
9. [Daftar API Endpoint](#9-daftar-api-endpoint)
10. [Troubleshooting](#10-troubleshooting)
11. [Keamanan Produksi](#11-keamanan-produksi)

---

## 1. Persyaratan Sistem

### Versi PHP yang Didukung

| Versi PHP | Status | Keterangan |
|-----------|--------|------------|
| PHP 7.0 | ✅ Didukung | Minimum, semua fitur berjalan |
| PHP 7.1 | ✅ Didukung | |
| PHP 7.2 | ✅ Didukung | |
| PHP 7.3 | ✅ Didukung | |
| PHP 7.4 | ✅ Didukung | |
| PHP 8.0 | ✅ Didukung | |
| PHP 8.1 | ✅ Didukung | |
| PHP 8.2 | ✅ Didukung | |
| PHP 8.3 | ✅ Didukung | |
| PHP 8.4 | ✅ Didukung | Direkomendasikan |

> **Catatan:** Tidak menggunakan fitur PHP 7.4+ seperti typed properties, `??=`, match expression, atau named arguments. Kode berjalan di semua versi PHP 7.0–8.4.

### Kebutuhan Hardware Minimum

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| OS | Ubuntu 18.04 / Debian 9 / CentOS 7 / Windows Server 2016 | Ubuntu 22.04 LTS |
| RAM | 256 MB | 512 MB+ |
| Disk | 200 MB | 1 GB+ |
| CPU | 1 Core | 2 Core+ |

### Ekstensi PHP yang Dibutuhkan

| Ekstensi | Fungsi | Wajib? |
|----------|--------|--------|
| `pdo` | Database abstraction layer | ✅ Wajib |
| `pdo_sqlite` | Driver SQLite | ✅ Wajib (mode SQLite) |
| `sqlite3` | SQLite native | ✅ Wajib (mode SQLite) |
| `json` | Parsing JSON request/response | ✅ Wajib |
| `mbstring` | Multi-byte string (format Rupiah) | ✅ Wajib |
| `openssl` | Enkripsi password | ✅ Wajib |
| `pdo_sqlsrv` | Driver SQL Server via PDO | ⚠️ Produksi saja |
| `sqlsrv` | Driver SQL Server native | ⚠️ Produksi saja |

> **Catatan:** `curl` tidak digunakan di server-side. Tidak membutuhkan Composer atau Node.js untuk menjalankan aplikasi.

---

## 2. Clone Repository

```bash
# Clone dari GitHub
git clone https://github.com/pt-zenity/BPDBALI-TRF.git lpd-canggu

# Masuk ke direktori project
cd lpd-canggu

# Buat folder yang diperlukan
mkdir -p data logs

# Set permission (Linux/Mac)
chmod 755 data logs
```

> **Tidak perlu `npm install` atau `composer install`** — aplikasi berjalan langsung dengan PHP tanpa dependensi eksternal.

---

## 3. Instalasi PHP & Ekstensi

### Ubuntu/Debian — PHP 7.x

> Gunakan PPA `ondrej/php` untuk instalasi PHP 7.x di Ubuntu modern.

```bash
# 1. Tambah PPA ondrej/php (mendukung semua versi PHP)
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

# 2. Install PHP 7.4 (atau ganti 7.4 dengan 7.0 / 7.1 / 7.2 / 7.3)
sudo apt-get install -y \
    php7.4-cli \
    php7.4-sqlite3 \
    php7.4-mbstring \
    php7.4-xml \
    php7.4-json \
    php7.4-openssl

# 3. Verifikasi
php --version
php -m | grep -E "sqlite|pdo|json|mbstring|openssl"
```

Contoh output:
```
PHP 7.4.33 (cli)
json
mbstring
openssl
pdo_sqlite
sqlite3
```

---

### Ubuntu/Debian — PHP 8.x

```bash
# 1. Tambah PPA ondrej/php (jika belum)
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

# 2. Install PHP 8.4 (atau ganti 8.4 dengan 8.0 / 8.1 / 8.2 / 8.3)
sudo apt-get install -y \
    php8.4-cli \
    php8.4-sqlite3 \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-openssl

# 3. Verifikasi
php --version
php -m | grep -E "sqlite|pdo|json|mbstring"
```

---

### CentOS / RHEL / AlmaLinux

```bash
# 1. Tambah Remi repository
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
sudo dnf module reset php
sudo dnf module enable php:remi-8.4   # Atau remi-7.4, remi-8.0, dst

# 2. Install PHP dan ekstensi
sudo dnf install -y php php-cli php-pdo php-sqlite3 php-mbstring php-json php-xml

# 3. Verifikasi
php --version
php -m | grep -E "sqlite|pdo|json|mbstring"
```

---

### Windows (XAMPP / Laragon)

**Menggunakan XAMPP:**

1. Download XAMPP dari https://www.apachefriends.org/ (pilih versi PHP 7.x atau 8.x)
2. Install ke `C:\xampp`
3. Pastikan ekstensi berikut aktif di `C:\xampp\php\php.ini` (hapus tanda `;`):
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   extension=mbstring
   extension=openssl
   ```
4. Restart Apache dari XAMPP Control Panel

**Menggunakan Laragon:**

1. Download Laragon dari https://laragon.org/download/
2. Pilih versi Full (sudah include PHP, MySQL, Apache, Nginx)
3. Klik kanan ikon Laragon → PHP → Pilih versi (7.x atau 8.x)
4. Ekstensi SQLite sudah aktif secara default

**Menggunakan PHP standalone (tanpa web server):**

```powershell
# Download PHP dari https://windows.php.net/download/
# Ekstrak ke C:\php

# Salin php.ini-development ke php.ini
copy C:\php\php.ini-development C:\php\php.ini

# Edit php.ini — hapus ; di depan baris berikut:
; extension=pdo_sqlite
; extension=sqlite3
; extension=mbstring
; extension=openssl

# Tambahkan C:\php ke PATH system environment

# Verifikasi
php --version
php -m | grep -E "sqlite|pdo|mbstring"
```

---

### Tambahan: SQL Server (Produksi)

Instalasi driver `sqlsrv` dan `pdo_sqlsrv` diperlukan **hanya** jika menggunakan SQL Server sebagai database produksi.

#### Linux (Ubuntu/Debian)

```bash
# 1. Tambah Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list \
    | sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt-get update

# 2. Install ODBC Driver 18
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev

# 3. Install PECL dan dependensi build PHP
sudo apt-get install -y php8.4-dev  # Atau php7.4-dev sesuai versi

# 4. Install ekstensi via PECL
sudo pecl install sqlsrv pdo_sqlsrv

# 5. Aktifkan ekstensi
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "extension=sqlsrv.so"     | sudo tee /etc/php/${PHP_VER}/cli/conf.d/30-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/${PHP_VER}/cli/conf.d/30-pdo_sqlsrv.ini

# 6. Verifikasi
php -m | grep sqlsrv
```

#### Windows

1. Download driver dari: https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
2. Copy file `.dll` yang sesuai versi PHP ke folder `ext/`:
   - PHP 7.4: `php_sqlsrv_74_ts.dll` dan `php_pdo_sqlsrv_74_ts.dll`
   - PHP 8.4: `php_sqlsrv_84_ts.dll` dan `php_pdo_sqlsrv_84_ts.dll`
3. Tambahkan di `php.ini`:
   ```ini
   extension=sqlsrv
   extension=pdo_sqlsrv
   ```

---

## 4. Konfigurasi Database

Salin file contoh konfigurasi:

```bash
cp .env.example .env
```

Lalu edit sesuai kebutuhan:

```bash
# Linux
nano .env

# Windows
notepad .env
```

---

### Mode A: SQLite (Lokal/Dev)

```ini
# .env — Mode SQLite (Default)
DB_CONNECTION=sqlite

# Path absolut ke file database (direkomendasikan)
SQLITE_PATH=/home/user/lpd-canggu/data/lpd_canggu.sqlite

# Windows:
# SQLITE_PATH=C:/xampp/htdocs/lpd-canggu/data/lpd_canggu.sqlite
```

> **SQLite adalah default** — jika file `.env` tidak ada, aplikasi otomatis menggunakan SQLite dengan path `data/lpd_canggu.sqlite` di dalam folder project.

---

### Mode B: SQL Server (Produksi)

```ini
# .env — Mode SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=192.168.1.100         # IP atau hostname SQL Server
DB_PORT=1433                   # Port default SQL Server
DB_DATABASE=Giosoft_LPD       # Nama database
DB_USERNAME=sa                 # Username SQL Server
DB_PASSWORD=#sa.lpd.Canggu.21 # Password SQL Server
```

> **Penting:** Pastikan SQL Server mengizinkan koneksi TCP/IP di port 1433 dan firewall sudah membuka akses dari server PHP.

---

## 5. Menjalankan Aplikasi

### Cara 1: PHP Built-in Server (Sederhana)

Cocok untuk **development** atau **testing** cepat. Tidak perlu instalasi web server.

```bash
# Masuk ke folder project
cd /path/ke/lpd-canggu

# Jalankan di port 8080
php -S 0.0.0.0:8080 router.php

# Akses di browser:
# http://localhost:8080
```

Hentikan dengan `Ctrl + C`.

**Windows:**
```powershell
cd C:\xampp\htdocs\lpd-canggu
php -S 0.0.0.0:8080 router.php
```

---

### Cara 2: Dengan PM2 (Daemon)

Cocok untuk **server Linux** agar proses tetap berjalan di background setelah logout.

```bash
# 1. Install Node.js & PM2 (jika belum ada)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt-get install -y nodejs
sudo npm install -g pm2

# 2. Cek path PHP
which php        # Contoh: /usr/bin/php

# 3. Edit ecosystem.config.cjs — sesuaikan PATH
nano ecosystem.config.cjs
```

Edit `ecosystem.config.cjs` dengan path yang sesuai:

```javascript
module.exports = {
  apps: [
    {
      name: 'lpd-php',
      script: '/usr/bin/php',                   // <- hasil `which php`
      args: '-S 0.0.0.0:8080 /FULL/PATH/lpd-canggu/router.php',
      cwd: '/FULL/PATH/lpd-canggu',              // <- path absolut folder project
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
# 4. Jalankan dengan PM2
pm2 start ecosystem.config.cjs

# 5. Cek status
pm2 list
pm2 logs lpd-php --nostream

# 6. Auto-start saat server reboot
pm2 startup
pm2 save
```

**Perintah PM2 berguna:**

```bash
pm2 restart lpd-php   # Restart aplikasi
pm2 stop lpd-php      # Hentikan sementara
pm2 delete lpd-php    # Hapus dari PM2
pm2 monit             # Monitor real-time (CPU & RAM)
pm2 logs lpd-php      # Lihat log (tekan Ctrl+C untuk keluar)
```

---

### Cara 3: Nginx + PHP-FPM (Produksi)

Cocok untuk **production server** dengan performa tinggi dan kemampuan HTTPS.

#### Install PHP-FPM

```bash
# Sesuaikan versi PHP (7.4, 8.1, 8.4, dst.)
sudo apt-get install -y php8.4-fpm

# Verifikasi
systemctl status php8.4-fpm
```

#### Konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/lpd-canggu
```

Isi dengan konfigurasi berikut:

```nginx
server {
    listen 80;
    server_name lpd.contoh.id;           # Ganti dengan domain atau IP Anda
    root /var/www/lpd-canggu;            # Path ke folder project

    index index.php;
    charset utf-8;

    # Semua request diarahkan ke router.php kecuali file statis
    location / {
        try_files $uri $uri/ /router.php$is_args$query_string;
    }

    # Static files (CSS, JS, gambar, font)
    location ~* \.(css|js|png|jpg|jpeg|ico|svg|woff2?|ttf|eot)$ {
        expires 7d;
        access_log off;
        add_header Cache-Control "public";
    }

    # PHP-FPM handler
    location ~ \.php$ {
        fastcgi_pass   unix:/run/php/php8.4-fpm.sock;  # Sesuaikan versi PHP
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 60;
    }

    # Sembunyikan file sensitif
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }

    # Larang akses langsung ke folder data dan logs
    location ~ ^/(data|logs)/ {
        deny all;
        return 404;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    # Log
    access_log /var/log/nginx/lpd-canggu-access.log;
    error_log  /var/log/nginx/lpd-canggu-error.log;
}
```

```bash
# Aktifkan site dan reload Nginx
sudo ln -s /etc/nginx/sites-available/lpd-canggu /etc/nginx/sites-enabled/
sudo nginx -t                        # Test konfigurasi
sudo systemctl reload nginx

# Izin folder data dan logs untuk www-data
sudo chown -R www-data:www-data /var/www/lpd-canggu/data
sudo chown -R www-data:www-data /var/www/lpd-canggu/logs
sudo chmod 755 /var/www/lpd-canggu/data /var/www/lpd-canggu/logs
```

#### Pasang SSL dengan Let's Encrypt (HTTPS gratis)

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d lpd.contoh.id
```

---

### Cara 4: Apache + mod_php

Cocok jika sudah menggunakan Apache (XAMPP, LAMP stack).

#### Linux (Apache + mod_php)

```bash
# Install Apache dan PHP
sudo apt-get install -y apache2 php8.4 libapache2-mod-php8.4 php8.4-sqlite3 php8.4-mbstring

# Aktifkan mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Buat file `.htaccess` di root project (jika belum ada):

```apache
# /var/www/html/lpd-canggu/.htaccess
Options -MultiViews
RewriteEngine On

# Arahkan semua request ke router.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ router.php [L,QSA]

# Larang akses ke folder sensitif
RewriteRule ^(data|logs)/ - [F,L]
RewriteRule \.env$ - [F,L]
```

Pastikan `AllowOverride All` aktif di konfigurasi Apache:

```apache
<Directory /var/www/html/lpd-canggu>
    AllowOverride All
</Directory>
```

```bash
sudo systemctl restart apache2
```

#### Windows (XAMPP)

1. Ekstrak project ke `C:\xampp\htdocs\lpd-canggu\`
2. Buat `.htaccess` dengan isi sama seperti di atas
3. Pastikan `mod_rewrite` aktif di `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
4. Akses via: `http://localhost/lpd-canggu/`

---

## 6. Inisialisasi Database

Setelah server berjalan, **wajib** menginisialisasi database terlebih dahulu (hanya sekali).

### Via Browser

1. Buka aplikasi di browser (contoh: `http://localhost:8080`)
2. Klik tombol **"⚙ Init DB"** di pojok kanan atas topbar
3. Tunggu hingga muncul notifikasi sukses

### Via curl (Terminal)

```bash
curl -X POST http://localhost:8080/api/init-db
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

> ✅ **Aman dijalankan berulang kali** — menggunakan `INSERT OR IGNORE`, sehingga data yang sudah ada tidak akan terhapus atau di-reset.

**Akun default setelah init:**

| Field | Nilai |
|-------|-------|
| Username | `admin` |
| Password | `admin123` |
| No. Rekening | `00.000000` |
| Status | Aktif |

> ⚠️ **Segera ganti password admin** setelah instalasi pertama!

---

## 7. Verifikasi Instalasi

Jalankan script berikut untuk memastikan semua endpoint berfungsi:

```bash
BASE="http://localhost:8080"

echo "========================================"
echo " LPD Canggu — Verifikasi Instalasi"
echo "========================================"

# 1. Cek halaman utama
echo ""
echo "1. Halaman Utama"
HTTP=$(curl -s -o /dev/null -w "%{http_code}" $BASE/)
echo "   HTTP Status: $HTTP $([ "$HTTP" = "200" ] && echo "✅" || echo "❌")"

# 2. Init Database
echo ""
echo "2. Inisialisasi Database"
INIT=$(curl -s -X POST $BASE/api/init-db)
STATUS=$(echo $INIT | python3 -c "import sys,json; print(json.load(sys.stdin).get('status','?'))" 2>/dev/null)
echo "   Status: $STATUS $([ "$STATUS" = "00" ] && echo "✅" || echo "❌")"

# 3. Login admin
echo ""
echo "3. Login Admin"
LOGIN=$(curl -s -X POST $BASE/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}')
STATUS=$(echo $LOGIN | python3 -c "import sys,json; print(json.load(sys.stdin).get('status','?'))" 2>/dev/null)
echo "   Status: $STATUS $([ "$STATUS" = "00" ] && echo "✅" || echo "❌")"

# 4. Daftar bank
echo ""
echo "4. Daftar Bank"
BANKS=$(curl -s $BASE/api/bank-list)
STATUS=$(echo $BANKS | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('status','?'), '-', len(d.get('data',[])), 'bank')" 2>/dev/null)
echo "   Status: $STATUS"

# 5. Dashboard
echo ""
echo "5. Dashboard"
DASH=$(curl -s $BASE/api/dashboard)
STATUS=$(echo $DASH | python3 -c "import sys,json; d=json.load(sys.stdin); s=d.get('stats',{}); print(d.get('status','?'), '- Nasabah:', s.get('total_nasabah',0))" 2>/dev/null)
echo "   Status: $STATUS"

echo ""
echo "========================================"
echo " Selesai! Akses: $BASE"
echo "========================================"
```

### Skenario Test Lengkap (Opsional)

```bash
BASE="http://localhost:8080"

# Buat nasabah baru
echo "--- Buat Nasabah ---"
curl -s -X POST $BASE/api/nasabah \
  -H "Content-Type: application/json" \
  -d '{"nama":"I Wayan Test","username":"wayan.test","pass_crypto":"test123456","phone":"081234567890"}' \
  | python3 -m json.tool

# Aktifkan nasabah (ganti 2 dengan ID dari response di atas)
echo "--- Aktifkan Nasabah ---"
curl -s -X PUT $BASE/api/nasabah/2/status \
  -H "Content-Type: application/json" \
  -d '{"status":"A"}' | python3 -m json.tool

# Setor tunai
echo "--- Setor Tunai ---"
curl -s -X POST $BASE/api/setor \
  -H "Content-Type: application/json" \
  -d '{"norek":"01.000002","amount":1000000,"remark":"Setoran awal"}' \
  | python3 -m json.tool

# Cek saldo
echo "--- Cek Saldo ---"
curl -s $BASE/api/saldo/01.000002 | python3 -m json.tool

# Tarik tunai
echo "--- Tarik Tunai ---"
curl -s -X POST $BASE/api/tarik \
  -H "Content-Type: application/json" \
  -d '{"norek":"01.000002","amount":200000,"remark":"Tarik kebutuhan"}' \
  | python3 -m json.tool

# Mutasi rekening
echo "--- Mutasi Rekening ---"
curl -s $BASE/api/mutasi/01.000002 | python3 -m json.tool
```

---

## 8. Struktur Direktori

```
lpd-canggu/
│
├── router.php                  ← Entry point & router utama
├── index.php                   ← Halaman SPA (HTML + Tailwind CSS + Vanilla JS)
├── .env                        ← Konfigurasi environment (buat dari .env.example)
├── .env.example                ← Contoh konfigurasi
├── .gitignore                  ← File yang diabaikan git
├── ecosystem.config.cjs        ← Konfigurasi PM2
├── INSTALL.md                  ← Dokumentasi instalasi ini
│
├── config/
│   └── database.php            ← Kelas DB, konstanta, inisialisasi schema & seed data
│
├── includes/
│   ├── bootstrap.php           ← Auto-load semua dependencies & init koneksi DB
│   └── helpers.php             ← Helper functions (json_ok, rp, trans_no, insert_folio, dll)
│
├── api/                        ← API Endpoints (satu file per endpoint)
│   ├── init.php                ← POST /api/init-db
│   ├── login.php               ← POST /api/login
│   ├── nasabah.php             ← CRUD /api/nasabah
│   ├── rekening.php            ← GET  /api/rekening/{norek}
│   ├── saldo.php               ← GET  /api/saldo/{norek}
│   ├── mutasi.php              ← GET  /api/mutasi/{norek}
│   ├── setor.php               ← POST /api/setor
│   ├── tarik.php               ← POST /api/tarik
│   ├── transfer_lpd.php        ← POST /api/transfer-lpd
│   ├── transfer_bank.php       ← POST /api/transfer-bank
│   ├── riwayat_transfer.php    ← GET  /api/riwayat-transfer/{norek}
│   ├── dashboard.php           ← GET  /api/dashboard
│   └── bank_list.php           ← GET  /api/bank-list
│
├── public/                     ← Static assets (CSS, JS, gambar)
│
├── data/                       ← Database SQLite (auto-created, tidak di-commit ke git)
│   └── lpd_canggu.sqlite
│
└── logs/                       ← Log aplikasi (auto-created, tidak di-commit ke git)
    ├── php_error.log
    ├── pm2-out.log
    └── pm2-error.log
```

---

## 9. Daftar API Endpoint

### Tabel Endpoint

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/api/init-db` | Inisialisasi / reset schema database |
| `POST` | `/api/login` | Login nasabah atau admin |
| `GET` | `/api/nasabah` | List semua nasabah (`?q=cari&status=A`) |
| `GET` | `/api/nasabah/{id}` | Detail satu nasabah beserta histori |
| `POST` | `/api/nasabah` | Tambah nasabah baru |
| `PUT` | `/api/nasabah/{id}` | Update data nasabah |
| `PUT` | `/api/nasabah/{id}/status` | Ubah status nasabah (`A`/`R`/`B`/`T`) |
| `DELETE` | `/api/nasabah/{id}` | Hapus nasabah (saldo harus 0) |
| `GET` | `/api/rekening/{norek}` | Info rekening + status nasabah |
| `GET` | `/api/saldo/{norek}` | Cek saldo rekening |
| `GET` | `/api/mutasi/{norek}` | Mutasi rekening (`?start_date=&end_date=&limit=&jenis=debit\|kredit`) |
| `POST` | `/api/setor` | Setor tunai ke rekening |
| `POST` | `/api/tarik` | Tarik tunai dari rekening |
| `POST` | `/api/transfer-lpd` | Transfer antar rekening dalam LPD |
| `POST` | `/api/transfer-bank` | Transfer ke bank lain (simulasi) |
| `GET` | `/api/riwayat-transfer/{norek}` | Riwayat transfer (`?jenis=LPD\|BANK&limit=20`) |
| `GET` | `/api/bank-list` | Daftar bank yang tersedia + biaya transfer |
| `GET` | `/api/dashboard` | Statistik sistem & transaksi terbaru |

---

### Contoh Request & Response

#### POST `/api/login` — Login
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```
```json
{
  "status": "00",
  "message": "Login berhasil",
  "user": {
    "id": 1,
    "nama": "Administrator LPD Canggu",
    "norek": "00.000000",
    "username": "admin",
    "status": "A",
    "saldo": 0,
    "saldo_fmt": "Rp 0"
  }
}
```

---

#### POST `/api/nasabah` — Tambah Nasabah
```bash
curl -X POST http://localhost:8080/api/nasabah \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "I Wayan Sudarsana",
    "username": "wayan.sudarsana",
    "pass_crypto": "password123",
    "pin_crypto": "112233",
    "phone": "081234567890",
    "email": "wayan@email.com",
    "alamat": "Br. Canggu, Kuta Utara, Badung"
  }'
```
```json
{
  "status": "00",
  "message": "Nasabah berhasil didaftarkan",
  "noid": "CG.000000002",
  "norek": "01.000002",
  "nama": "I Wayan Sudarsana",
  "status": "R",
  "note": "Status Registrasi. Aktifkan nasabah untuk mulai bertransaksi."
}
```

---

#### PUT `/api/nasabah/{id}/status` — Ubah Status
```bash
curl -X PUT http://localhost:8080/api/nasabah/2/status \
  -H "Content-Type: application/json" \
  -d '{"status":"A"}'
```

**Status yang tersedia:**

| Kode | Keterangan |
|------|------------|
| `A` | Aktif — dapat bertransaksi |
| `R` | Registrasi — menunggu aktivasi |
| `B` | Blokir — akun dibekukan |
| `T` | Tutup — akun ditutup permanen |

---

#### POST `/api/setor` — Setor Tunai
```bash
curl -X POST http://localhost:8080/api/setor \
  -H "Content-Type: application/json" \
  -d '{"norek":"01.000002","amount":1000000,"remark":"Setor tunai"}'
```
```json
{
  "status": "00",
  "message": "Setor tunai berhasil",
  "trans_no": "ST260423xxxx",
  "norek": "01.000002",
  "nama": "I Wayan Sudarsana",
  "jumlah": 1000000,
  "jumlah_fmt": "Rp 1.000.000",
  "saldo": 1000000,
  "saldo_fmt": "Rp 1.000.000"
}
```

---

#### POST `/api/tarik` — Tarik Tunai
```bash
curl -X POST http://localhost:8080/api/tarik \
  -H "Content-Type: application/json" \
  -d '{"norek":"01.000002","amount":200000,"remark":"Tarik tunai"}'
```

---

#### POST `/api/transfer-lpd` — Transfer Antar LPD
```bash
curl -X POST http://localhost:8080/api/transfer-lpd \
  -H "Content-Type: application/json" \
  -d '{
    "from_norek": "01.000002",
    "to_norek": "01.000003",
    "amount": 200000,
    "remark": "Bayar cicilan"
  }'
```

---

#### POST `/api/transfer-bank` — Transfer ke Bank Lain
```bash
curl -X POST http://localhost:8080/api/transfer-bank \
  -H "Content-Type: application/json" \
  -d '{
    "from_norek": "01.000002",
    "bank_code": "014",
    "bank_acc": "1234567890",
    "to_name": "Budi Santoso",
    "amount": 500000,
    "remark": "Pembayaran"
  }'
```

**Kode bank yang tersedia:**

| Kode | Nama Bank | Biaya Transfer |
|------|-----------|---------------|
| `014` | BCA | Rp 5.000 |
| `008` | Mandiri | Rp 5.000 |
| `009` | BNI | Rp 5.000 |
| `002` | BRI | Rp 5.000 |
| `213` | BPD Bali | Rp 3.500 |
| `110` | Bank Sinar | Rp 3.500 |
| `011` | Danamon | Rp 6.500 |
| `022` | CIMB Niaga | Rp 6.500 |
| `013` | Permata Bank | Rp 6.500 |
| `016` | Maybank | Rp 6.500 |
| `019` | Panin Bank | Rp 6.500 |
| `028` | OCBC NISP | Rp 6.500 |

---

### Kode Status Response

| Status | HTTP | Keterangan |
|--------|------|------------|
| `00` | 200 | Sukses |
| `01` | 400 | Data tidak lengkap / input tidak valid |
| `02` | 409 | Data sudah ada / duplikat |
| `03` | 401 | Username / password salah |
| `04` | 404 | Data tidak ditemukan |
| `05` | 400 | Rekening / nasabah tidak aktif |
| `06` | 400 | Operasi tidak diizinkan (misal: hapus dengan saldo) |
| `07` | 403 | Akun diblokir / ditutup |
| `51` | 400 | Saldo tidak mencukupi |
| `99` | 500 | Server error |

### Batasan Transaksi Default

| Parameter | Nilai |
|-----------|-------|
| Saldo Minimum Mengendap | Rp 50.000 |
| Transfer Minimum | Rp 10.000 |
| Transfer Maksimum | Rp 5.000.000 |
| Setoran Minimum | Rp 1.000 |
| Penarikan Minimum | Rp 10.000 |

---

## 10. Troubleshooting

### ❌ Error: `pdo_sqlite extension not found`
```bash
# Ubuntu/Debian
sudo apt-get install php8.4-sqlite3   # ganti 8.4 sesuai versi PHP

# CentOS/RHEL
sudo dnf install php-sqlite3

# Verifikasi
php -m | grep -i sqlite
```

---

### ❌ Error: `php: command not found`
```bash
# Ubuntu/Debian — cari PHP yang terinstall
which php7.4 php8.0 php8.1 php8.4

# Buat symlink
sudo ln -s /usr/bin/php8.4 /usr/local/bin/php

# Windows — pastikan PATH sudah berisi folder PHP
# System Properties → Environment Variables → Path → tambahkan C:\php
```

---

### ❌ Error: `Cannot connect to SQL Server (HYT00)`

Kemungkinan penyebab:

1. SQL Server tidak berjalan:
   ```bash
   # Windows
   services.msc → SQL Server (MSSQLSERVER)

   # Linux
   systemctl status mssql-server
   ```
2. Port 1433 diblokir firewall:
   ```bash
   sudo ufw allow 1433
   nc -zv <DB_HOST> 1433   # Test koneksi
   ```
3. TCP/IP belum aktif → Buka SQL Server Configuration Manager → Network Configuration → Enable TCP/IP
4. `DB_HOST` salah → Test: `ping <DB_HOST>`

---

### ❌ Error: `Permission denied` pada folder `data/` atau `logs/`
```bash
# Jika dijalankan sebagai user biasa
chmod 755 data/ logs/

# Jika menggunakan Nginx (www-data)
sudo chown -R www-data:www-data data/ logs/
sudo chmod 755 data/ logs/

# Jika masih gagal (mode permissive sementara)
chmod 777 data/ logs/
```

---

### ❌ Error: Port 8080 sudah digunakan
```bash
# Cari dan hentikan proses
fuser -k 8080/tcp

# Atau ganti port
php -S 0.0.0.0:9090 router.php
```

---

### ❌ Halaman kosong atau `500 Internal Server Error`
```bash
# Aktifkan error display sementara
php -d display_errors=1 -S 0.0.0.0:8080 router.php

# Atau cek log
cat logs/php_error.log
tail -f logs/pm2-error.log   # Jika menggunakan PM2
```

---

### ❌ API return `{"status":"404","message":"Endpoint tidak ditemukan"}`

Pastikan HTTP method yang digunakan sesuai:

```bash
# ✅ Benar
curl -X POST http://localhost:8080/api/setor ...
curl http://localhost:8080/api/saldo/01.000002

# ❌ Salah
curl http://localhost:8080/api/setor   # GET untuk endpoint POST
```

---

### ❌ Data hilang setelah restart (SQLite)

Pastikan `SQLITE_PATH` menggunakan path **absolut** di `.env`:

```ini
# ✅ Benar — path absolut
SQLITE_PATH=/home/user/lpd-canggu/data/lpd_canggu.sqlite

# ❌ Salah — path relatif bisa berubah tergantung working directory
SQLITE_PATH=data/lpd_canggu.sqlite
```

---

### ❌ Init DB gagal saat menggunakan SQL Server

```bash
# Cek ekstensi sqlsrv sudah aktif
php -m | grep -i sqlsrv

# Test koneksi manual
php -r "
\$dsn = 'sqlsrv:Server=HOST,1433;Database=DB;TrustServerCertificate=1';
try {
    \$pdo = new PDO(\$dsn, 'USER', 'PASS');
    echo 'Koneksi berhasil!';
} catch(PDOException \$e) {
    echo 'Gagal: ' . \$e->getMessage();
}
"
```

---

## 11. Keamanan Produksi

1. **Ganti password admin default** segera setelah instalasi:
   - Login sebagai admin → ubah password melalui fitur ganti password
   - Atau langsung update di database:
     ```bash
     sqlite3 data/lpd_canggu.sqlite \
       "UPDATE gmob_nasabah SET pass_crypto='HASH_BARU' WHERE username='admin'"
     ```

2. **Jangan expose `.env`** ke publik:
   - Pastikan Nginx/Apache memblokir akses ke `.env`
   - Sudah dikonfigurasi di contoh Nginx di atas

3. **Gunakan HTTPS** di production:
   ```bash
   sudo certbot --nginx -d domain.anda.id
   ```

4. **Backup database SQLite** secara rutin:
   ```bash
   # Tambahkan ke crontab (cron job setiap malam jam 02:00)
   0 2 * * * cp /path/lpd-canggu/data/lpd_canggu.sqlite \
     /backup/lpd_$(date +\%Y\%m\%d).sqlite
   ```

5. **Batasi akses** endpoint `/api/init-db` setelah instalasi selesai (hindari reset tidak sengaja):
   - Nonaktifkan route di `router.php` atau batasi dengan IP whitelist di Nginx

6. **Set `display_errors = Off`** di production `php.ini`:
   ```ini
   display_errors = Off
   log_errors = On
   error_log = /path/ke/logs/php_error.log
   ```

---

## 📞 Informasi Repository

| Item | Detail |
|------|--------|
| **Repository** | https://github.com/pt-zenity/BPDBALI-TRF |
| **Branch** | `main` |
| **Bahasa** | PHP 7.0 — PHP 8.4 (tanpa framework) |
| **Database** | SQLite (dev) / SQL Server MSSQL (produksi) |
| **Frontend** | HTML + Tailwind CSS CDN + Vanilla JS |
| **Dependensi** | Tidak ada (no Composer, no npm) |
| **Lisensi** | Proprietary — PT Zenity |
