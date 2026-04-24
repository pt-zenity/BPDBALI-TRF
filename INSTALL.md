# 📦 Panduan Instalasi — LPD Canggu Sistem Transaksi

> Sistem transaksi perbankan LPD (Lembaga Perkreditan Desa) Canggu berbasis PHP murni  
> Support database: **SQLite** (default/lokal) dan **SQL Server / MSSQL** (produksi)  
> Kompatibel: **PHP 7.0 — PHP 8.4** · **Apache 2.2 & 2.4** · Tanpa framework · Tanpa Composer

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
   - [⭐ Cara 1: Apache + mod\_php (Direkomendasikan)](#cara-1-apache--mod_php-direkomendasikan)
   - [Cara 2: Apache + PHP-FPM (Produksi Tinggi)](#cara-2-apache--php-fpm-produksi-tinggi)
   - [Cara 3: XAMPP / Laragon (Windows)](#cara-3-xampp--laragon-windows)
   - [Cara 4: PHP Built-in Server (Dev Cepat)](#cara-4-php-built-in-server-dev-cepat)
   - [Cara 5: Nginx + PHP-FPM (Alternatif)](#cara-5-nginx--php-fpm-alternatif)
6. [Inisialisasi Database](#6-inisialisasi-database)
7. [Verifikasi Instalasi](#7-verifikasi-instalasi)
8. [Struktur Direktori & File Konfigurasi](#8-struktur-direktori--file-konfigurasi)
9. [Daftar API Endpoint](#9-daftar-api-endpoint)
10. [Troubleshooting](#10-troubleshooting)
11. [Keamanan Produksi](#11-keamanan-produksi)

---

## 1. Persyaratan Sistem

### Versi PHP yang Didukung

| Versi PHP | Status | Keterangan |
|-----------|--------|------------|
| PHP 7.0 | ✅ Didukung | Minimum |
| PHP 7.1 – 7.3 | ✅ Didukung | |
| PHP 7.4 | ✅ Didukung | |
| PHP 8.0 – 8.3 | ✅ Didukung | |
| PHP 8.4 | ✅ Didukung | Direkomendasikan |

### Web Server yang Didukung

| Web Server | Status | Keterangan |
|------------|--------|------------|
| **Apache 2.2** | ✅ Didukung | Butuh `mod_rewrite` |
| **Apache 2.4** | ✅ Didukung | **Direkomendasikan** |
| PHP Built-in Server | ✅ Didukung | Untuk development saja |
| Nginx | ✅ Didukung | Lihat Cara 5 |

### Kebutuhan Hardware Minimum

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| OS | Ubuntu 18.04 / Debian 9 / CentOS 7 / Windows 7+ | Ubuntu 22.04 LTS |
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
| `mbstring` | Format Rupiah (number_format) | ✅ Wajib |
| `openssl` | Enkripsi password | ✅ Wajib |
| `mod_rewrite` | URL routing Apache | ✅ Wajib (Apache) |
| `pdo_sqlsrv` | Driver SQL Server via PDO | ⚠️ Produksi saja |
| `sqlsrv` | Driver SQL Server native | ⚠️ Produksi saja |

---

## 2. Clone Repository

```bash
# Clone dari GitHub
git clone https://github.com/pt-zenity/BPDBALI-TRF.git lpd-canggu

# Masuk ke direktori project
cd lpd-canggu

# Buat folder yang diperlukan (jika belum ada)
mkdir -p data logs

# Set permission (Linux/Mac)
chmod 755 data logs
```

> **Tidak perlu `npm install` atau `composer install`** — berjalan langsung dengan PHP + Apache.

> **`.htaccess` sudah tersedia** di repository — tidak perlu dibuat manual.

---

## 3. Instalasi PHP & Ekstensi

### Ubuntu/Debian — PHP 7.x

```bash
# 1. Tambah PPA ondrej/php (mendukung semua versi PHP)
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

# 2. Install PHP 7.4 beserta ekstensi (ganti 7.4 → 7.0 / 7.1 / 7.2 / 7.3 sesuai kebutuhan)
sudo apt-get install -y \
    php7.4 \
    php7.4-cli \
    php7.4-sqlite3 \
    php7.4-mbstring \
    php7.4-xml \
    php7.4-json \
    libapache2-mod-php7.4

# 3. Verifikasi
php --version
php -m | grep -E "sqlite|pdo|json|mbstring"
```

---

### Ubuntu/Debian — PHP 8.x

```bash
# 1. Tambah PPA ondrej/php (jika belum)
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

# 2. Install PHP 8.4 beserta ekstensi (ganti 8.4 → 8.0 / 8.1 / 8.2 / 8.3 sesuai kebutuhan)
sudo apt-get install -y \
    php8.4 \
    php8.4-cli \
    php8.4-sqlite3 \
    php8.4-mbstring \
    php8.4-xml \
    libapache2-mod-php8.4

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
sudo dnf module enable php:remi-8.4   # Atau remi-7.4, remi-8.0, dst.

# 2. Install PHP + Apache + ekstensi
sudo dnf install -y httpd php php-cli php-pdo php-sqlite3 php-mbstring php-json php-xml

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
4. Pastikan `mod_rewrite` aktif di `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
5. Restart Apache dari XAMPP Control Panel

**Menggunakan Laragon:**

1. Download dari https://laragon.org/download/ (pilih versi Full)
2. Install dan jalankan Laragon
3. Klik kanan ikon Laragon di system tray → PHP → Pilih versi (7.x atau 8.x)
4. `mod_rewrite` dan SQLite sudah aktif secara default

---

### Tambahan: SQL Server (Produksi)

Driver `sqlsrv` diperlukan **hanya** jika menggunakan SQL Server sebagai database produksi.

#### Linux

```bash
# 1. Tambah Microsoft repository
curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -
curl https://packages.microsoft.com/config/ubuntu/22.04/prod.list \
    | sudo tee /etc/apt/sources.list.d/mssql-release.list
sudo apt-get update

# 2. Install ODBC Driver 18
sudo ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev

# 3. Install PECL + dependensi build
sudo apt-get install -y php8.4-dev   # Sesuaikan versi PHP

# 4. Install ekstensi via PECL
sudo pecl install sqlsrv pdo_sqlsrv

# 5. Aktifkan ekstensi
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "extension=sqlsrv.so"     | sudo tee /etc/php/${PHP_VER}/apache2/conf.d/30-sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/${PHP_VER}/apache2/conf.d/30-pdo_sqlsrv.ini

# 6. Restart Apache
sudo systemctl restart apache2
```

#### Windows

1. Download driver dari: https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server
2. Copy `.dll` yang sesuai versi PHP ke folder `ext/`:
   - PHP 7.4: `php_sqlsrv_74_ts.dll` + `php_pdo_sqlsrv_74_ts.dll`
   - PHP 8.4: `php_sqlsrv_84_ts.dll` + `php_pdo_sqlsrv_84_ts.dll`
3. Tambahkan di `php.ini`:
   ```ini
   extension=sqlsrv
   extension=pdo_sqlsrv
   ```
4. Restart Apache

---

## 4. Konfigurasi Database

Salin file contoh konfigurasi lalu sesuaikan:

```bash
cp .env.example .env
nano .env        # Linux
# notepad .env  # Windows
```

### Mode A: SQLite (Lokal/Dev)

```ini
# .env — Mode SQLite (Default)
DB_CONNECTION=sqlite

# Path ABSOLUT ke file database
SQLITE_PATH=/var/www/html/lpd-canggu/data/lpd_canggu.sqlite

# Windows XAMPP:
# SQLITE_PATH=C:/xampp/htdocs/lpd-canggu/data/lpd_canggu.sqlite
```

> Jika `.env` tidak ada, aplikasi otomatis menggunakan SQLite dengan path `data/lpd_canggu.sqlite` di dalam folder project.

### Mode B: SQL Server (Produksi)

```ini
# .env — Mode SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=192.168.1.100
DB_PORT=1433
DB_DATABASE=Giosoft_LPD
DB_USERNAME=sa
DB_PASSWORD=#sa.lpd.Canggu.21
```

---

## 5. Menjalankan Aplikasi

### Cara 1: Apache + mod_php (Direkomendasikan)

Cara paling umum dan mudah. `mod_php` berjalan langsung dalam proses Apache — tidak perlu konfigurasi PHP-FPM terpisah.

#### Install Apache + mod_php

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y apache2 libapache2-mod-php8.4 php8.4-sqlite3 php8.4-mbstring

# Aktifkan modul yang diperlukan
sudo a2enmod rewrite headers expires deflate
sudo systemctl restart apache2
```

#### Tempatkan File Project

```bash
# Salin project ke DocumentRoot Apache
sudo cp -r lpd-canggu /var/www/html/lpd-canggu

# Set permission folder data dan logs
sudo chown -R www-data:www-data /var/www/html/lpd-canggu/data
sudo chown -R www-data:www-data /var/www/html/lpd-canggu/logs
sudo chmod 755 /var/www/html/lpd-canggu/data
sudo chmod 755 /var/www/html/lpd-canggu/logs
```

#### Buat Virtual Host Apache

```bash
sudo nano /etc/apache2/sites-available/lpd-canggu.conf
```

Isi konfigurasi Virtual Host:

```apache
<VirtualHost *:80>
    ServerName lpd.contoh.id
    # ServerAlias www.lpd.contoh.id   # Aktifkan jika pakai www

    DocumentRoot /var/www/html/lpd-canggu
    DirectoryIndex index.php

    <Directory /var/www/html/lpd-canggu>
        # Izinkan .htaccess berfungsi
        AllowOverride All
        Options -Indexes -MultiViews +FollowSymLinks

        # Apache 2.4
        Require all granted

        # Apache 2.2 (gunakan ini jika pakai Apache lama)
        # Order allow,deny
        # Allow from all
    </Directory>

    # Log
    ErrorLog  ${APACHE_LOG_DIR}/lpd-canggu-error.log
    CustomLog ${APACHE_LOG_DIR}/lpd-canggu-access.log combined
</VirtualHost>
```

```bash
# Aktifkan site dan reload Apache
sudo a2ensite lpd-canggu.conf
sudo a2dissite 000-default.conf   # Nonaktifkan default site (opsional)
sudo apache2ctl configtest        # Test konfigurasi
sudo systemctl reload apache2

# Akses di browser:
# http://lpd.contoh.id
# atau http://IP_SERVER/lpd-canggu/ (jika tanpa Virtual Host)
```

#### Akses di Sub-folder (Tanpa Virtual Host)

Jika ingin akses via `http://localhost/lpd-canggu/` tanpa Virtual Host:

```bash
# Tempatkan di htdocs/DocumentRoot
sudo cp -r lpd-canggu /var/www/html/lpd-canggu

# Buka browser
# http://localhost/lpd-canggu/
```

> **Penting:** Edit `.htaccess` root — ubah `RewriteBase` sesuai sub-folder:
> ```apache
> # Dari:
> RewriteBase /
> # Menjadi (sesuai nama folder):
> RewriteBase /lpd-canggu/
> ```

---

### Cara 2: Apache + PHP-FPM (Produksi Tinggi)

Lebih performa untuk server dengan traffic tinggi. PHP-FPM berjalan sebagai proses terpisah.

#### Install

```bash
# Install Apache + PHP-FPM (sesuaikan versi PHP)
sudo apt-get install -y apache2 php8.4-fpm php8.4-sqlite3 php8.4-mbstring

# Aktifkan modul yang diperlukan
sudo a2enmod rewrite proxy_fcgi setenvif headers expires
sudo a2enconf php8.4-fpm
sudo systemctl restart apache2 php8.4-fpm
```

#### Virtual Host untuk PHP-FPM

```bash
sudo nano /etc/apache2/sites-available/lpd-canggu.conf
```

```apache
<VirtualHost *:80>
    ServerName lpd.contoh.id
    DocumentRoot /var/www/html/lpd-canggu
    DirectoryIndex index.php

    <Directory /var/www/html/lpd-canggu>
        AllowOverride All
        Options -Indexes -MultiViews +FollowSymLinks
        Require all granted
    </Directory>

    # Arahkan PHP ke PHP-FPM socket
    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php8.4-fpm.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog  ${APACHE_LOG_DIR}/lpd-canggu-error.log
    CustomLog ${APACHE_LOG_DIR}/lpd-canggu-access.log combined
</VirtualHost>
```

```bash
sudo a2ensite lpd-canggu.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

### Cara 3: XAMPP / Laragon (Windows)

Cara tercepat untuk instalasi di Windows — cocok untuk development maupun server Windows.

#### XAMPP (Windows)

1. Download & install XAMPP dari https://www.apachefriends.org/
2. Jalankan XAMPP Control Panel, klik **Start** pada Apache
3. Salin folder project ke `C:\xampp\htdocs\lpd-canggu\`
4. Pastikan `mod_rewrite` aktif — cek `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
   Jika ada tanda `#` di depan, hapus, lalu restart Apache
5. Pastikan `AllowOverride All` aktif:
   ```apache
   <Directory "C:/xampp/htdocs">
       AllowOverride All
       ...
   </Directory>
   ```
6. Akses: **http://localhost/lpd-canggu/**

> **Edit `.htaccess` root** — ubah `RewriteBase` menjadi `/lpd-canggu/`:
> ```apache
> RewriteBase /lpd-canggu/
> ```

#### Laragon (Windows)

1. Download & install Laragon dari https://laragon.org/download/
2. Jalankan Laragon, klik **Start All**
3. Salin folder project ke `C:\laragon\www\lpd-canggu\`
4. Laragon otomatis membuat Virtual Host — akses: **http://lpd-canggu.test/**
5. `mod_rewrite` sudah aktif secara default, `RewriteBase /` sudah sesuai

---

### Cara 4: PHP Built-in Server (Dev Cepat)

Untuk testing cepat tanpa perlu install Apache. **Jangan digunakan di production.**

```bash
cd /path/ke/lpd-canggu

# Jalankan di port 8080
php -S 0.0.0.0:8080 router.php

# Akses: http://localhost:8080
```

> File `router.php` sudah dikonfigurasi khusus untuk PHP built-in server.  
> Mode ini **tidak menggunakan** `.htaccess`.

**Dengan PM2 (daemon background):**

```bash
# Install PM2
sudo npm install -g pm2

# Jalankan
pm2 start ecosystem.config.cjs

# Auto-start saat reboot
pm2 startup && pm2 save
```

---

### Cara 5: Nginx + PHP-FPM (Alternatif)

Jika ingin menggunakan Nginx sebagai alternatif Apache.

```bash
sudo apt-get install -y nginx php8.4-fpm php8.4-sqlite3 php8.4-mbstring
```

```bash
sudo nano /etc/nginx/sites-available/lpd-canggu
```

```nginx
server {
    listen 80;
    server_name lpd.contoh.id;
    root /var/www/html/lpd-canggu;
    index index.php;

    # Arahkan semua request ke router.php
    location / {
        try_files $uri $uri/ /router.php$is_args$query_string;
    }

    # Static files
    location ~* \.(css|js|png|jpg|ico|svg|woff2?)$ {
        expires 7d;
        access_log off;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass   unix:/run/php/php8.4-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    # Larang akses file & folder sensitif
    location ~ ^/(data|logs|config|includes|api)/ { deny all; }
    location ~ /\.(env|git|htaccess|user\.ini)    { deny all; }

    error_log  /var/log/nginx/lpd-canggu-error.log;
    access_log /var/log/nginx/lpd-canggu-access.log;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/lpd-canggu /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

> Untuk Nginx, file `.htaccess` **tidak digunakan** — routing ditangani oleh konfigurasi Nginx di atas.

---

## 6. Inisialisasi Database

Setelah server berjalan, inisialisasi database terlebih dahulu (hanya sekali).

### Via Browser

1. Buka aplikasi di browser (contoh: `http://lpd.contoh.id` atau `http://localhost/lpd-canggu/`)
2. Klik tombol **"⚙ Init DB"** di pojok kanan atas topbar
3. Tunggu hingga muncul notifikasi sukses

### Via curl

```bash
# Apache / Nginx
curl -X POST http://lpd.contoh.id/api/init-db

# XAMPP sub-folder
curl -X POST http://localhost/lpd-canggu/api/init-db

# PHP built-in server
curl -X POST http://localhost:8080/api/init-db
```

Respons sukses:

```json
{
  "status": "00",
  "message": "Database berhasil diinisialisasi",
  "driver": "sqlite",
  "path": "/var/www/html/lpd-canggu/data/lpd_canggu.sqlite",
  "detail": [
    "gmob_nasabah OK", "gmob_rekening OK", "gtb_nasabah OK",
    "gtb_folio OK", "gmob_transfer OK", "gcore_bankcode OK",
    "gmob_token OK", "gmob_log_trans OK",
    "Bank codes seeded OK", "Admin seeded OK"
  ]
}
```

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

```bash
# Ganti BASE sesuai cara yang dipakai:
BASE="http://lpd.contoh.id"          # Virtual Host
# BASE="http://localhost/lpd-canggu" # XAMPP sub-folder
# BASE="http://localhost:8080"        # PHP built-in

echo "========================================"
echo " LPD Canggu — Verifikasi Instalasi"
echo "========================================"

# 1. Halaman utama
HTTP=$(curl -s -o /dev/null -w "%{http_code}" $BASE/)
echo "1. Halaman Utama : HTTP $HTTP $([ "$HTTP" = "200" ] && echo "✅" || echo "❌")"

# 2. Init DB
STATUS=$(curl -s -X POST $BASE/api/init-db | python3 -c "import sys,json; print(json.load(sys.stdin).get('status','ERR'))" 2>/dev/null)
echo "2. Init Database : $STATUS $([ "$STATUS" = "00" ] && echo "✅" || echo "❌")"

# 3. Login admin
STATUS=$(curl -s -X POST $BASE/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin).get('status','ERR'))" 2>/dev/null)
echo "3. Login Admin   : $STATUS $([ "$STATUS" = "00" ] && echo "✅" || echo "❌")"

# 4. Daftar bank
RESULT=$(curl -s $BASE/api/bank-list | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('status','ERR'), '-', len(d.get('data',[])), 'bank')" 2>/dev/null)
echo "4. Daftar Bank   : $RESULT"

# 5. Dashboard
RESULT=$(curl -s $BASE/api/dashboard | python3 -c "import sys,json; d=json.load(sys.stdin); s=d.get('stats',{}); print(d.get('status','ERR'), '- Nasabah:', s.get('total_nasabah',0))" 2>/dev/null)
echo "5. Dashboard     : $RESULT"

echo "========================================"
echo " Selesai! Akses: $BASE"
echo "========================================"
```

---

## 8. Struktur Direktori & File Konfigurasi

```
lpd-canggu/
│
├── .htaccess                   ← ⭐ Routing Apache (RewriteRule → router.php)
├── .user.ini                   ← PHP settings untuk PHP-FPM
├── router.php                  ← Router untuk PHP built-in server
├── index.php                   ← Halaman SPA (HTML + Tailwind CSS + Vanilla JS)
├── .env                        ← Konfigurasi environment (buat dari .env.example)
├── .env.example                ← Contoh konfigurasi
├── .gitignore
├── ecosystem.config.cjs        ← Konfigurasi PM2 (untuk PHP built-in server)
├── INSTALL.md                  ← Dokumentasi instalasi ini
│
├── config/
│   ├── .htaccess               ← Larang akses langsung dari browser
│   └── database.php            ← Kelas DB, konstanta, schema & seed data
│
├── includes/
│   ├── .htaccess               ← Larang akses langsung dari browser
│   ├── bootstrap.php           ← Auto-load semua dependencies & init koneksi DB
│   └── helpers.php             ← Helper functions (json_ok, rp, trans_no, dll)
│
├── api/
│   ├── .htaccess               ← Larang akses langsung dari browser
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
│   ├── css/
│   └── js/
│
├── data/
│   ├── .htaccess               ← Larang akses file SQLite dari browser
│   └── lpd_canggu.sqlite       ← Database (auto-created, tidak di-commit)
│
└── logs/
    ├── .htaccess               ← Larang akses log dari browser
    └── php_error.log           ← Log PHP error (tidak di-commit)
```

### Penjelasan File `.htaccess`

| File | Fungsi |
|------|--------|
| `.htaccess` (root) | Routing semua request ke `router.php` via `mod_rewrite` |
| `api/.htaccess` | Blokir akses langsung ke file PHP (harus lewat router) |
| `config/.htaccess` | Blokir akses folder konfigurasi database |
| `includes/.htaccess` | Blokir akses folder helper & bootstrap |
| `data/.htaccess` | Blokir akses file SQLite dari browser |
| `logs/.htaccess` | Blokir akses file log dari browser |

---

## 9. Daftar API Endpoint

### Tabel Endpoint

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| `POST` | `/api/init-db` | Inisialisasi schema database |
| `POST` | `/api/login` | Login nasabah atau admin |
| `GET` | `/api/nasabah` | List semua nasabah (`?q=cari&status=A`) |
| `GET` | `/api/nasabah/{id}` | Detail satu nasabah + histori |
| `POST` | `/api/nasabah` | Tambah nasabah baru |
| `PUT` | `/api/nasabah/{id}` | Update data nasabah |
| `PUT` | `/api/nasabah/{id}/status` | Ubah status (`A`/`R`/`B`/`T`) |
| `DELETE` | `/api/nasabah/{id}` | Hapus nasabah (saldo harus 0) |
| `GET` | `/api/rekening/{norek}` | Info rekening + status nasabah |
| `GET` | `/api/saldo/{norek}` | Cek saldo rekening |
| `GET` | `/api/mutasi/{norek}` | Mutasi rekening (`?start_date=&end_date=&limit=&jenis=`) |
| `POST` | `/api/setor` | Setor tunai ke rekening |
| `POST` | `/api/tarik` | Tarik tunai dari rekening |
| `POST` | `/api/transfer-lpd` | Transfer antar rekening LPD |
| `POST` | `/api/transfer-bank` | Transfer ke bank lain (simulasi) |
| `GET` | `/api/riwayat-transfer/{norek}` | Riwayat transfer (`?jenis=LPD\|BANK&limit=20`) |
| `GET` | `/api/bank-list` | Daftar bank + biaya transfer |
| `GET` | `/api/dashboard` | Statistik & transaksi terbaru |

### Contoh Request

**POST `/api/nasabah`** — Tambah Nasabah
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

**POST `/api/setor`** — Setor Tunai
```json
{ "norek": "01.000002", "amount": 1000000, "remark": "Setor tunai" }
```

**POST `/api/tarik`** — Tarik Tunai
```json
{ "norek": "01.000002", "amount": 200000, "remark": "Tarik tunai" }
```

**POST `/api/transfer-lpd`** — Transfer Antar LPD
```json
{ "from_norek": "01.000002", "to_norek": "01.000003", "amount": 100000 }
```

**POST `/api/transfer-bank`** — Transfer ke Bank Lain
```json
{
  "from_norek": "01.000002",
  "bank_code": "014",
  "bank_acc": "1234567890",
  "to_name": "Budi Santoso",
  "amount": 500000
}
```

### Kode Status Response

| Status | HTTP | Keterangan |
|--------|------|------------|
| `00` | 200 | Sukses |
| `01` | 400 | Input tidak valid / data tidak lengkap |
| `02` | 409 | Duplikat data |
| `03` | 401 | Username / password salah |
| `04` | 404 | Data tidak ditemukan |
| `05` | 400 | Rekening / nasabah tidak aktif |
| `06` | 400 | Operasi tidak diizinkan |
| `07` | 403 | Akun diblokir / ditutup |
| `51` | 400 | Saldo tidak mencukupi |
| `99` | 500 | Server error |

### Batasan Transaksi Default

| Parameter | Nilai |
|-----------|-------|
| Saldo minimum mengendap | Rp 50.000 |
| Transfer minimum | Rp 10.000 |
| Transfer maksimum | Rp 5.000.000 |
| Setoran minimum | Rp 1.000 |
| Penarikan minimum | Rp 10.000 |

---

## 10. Troubleshooting

### ❌ `404 Not Found` — Semua halaman (mod_rewrite tidak aktif)

```bash
# Cek mod_rewrite sudah aktif
apache2ctl -M | grep rewrite

# Aktifkan jika belum ada
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Pastikan `AllowOverride All` ada di blok `<Directory>` Virtual Host — bukan `AllowOverride None`.

---

### ❌ `403 Forbidden` saat akses index

```bash
# Cek permission folder
ls -la /var/www/html/lpd-canggu/

# Set permission yang benar
sudo chmod 755 /var/www/html/lpd-canggu
sudo chmod 644 /var/www/html/lpd-canggu/*.php
sudo chmod 644 /var/www/html/lpd-canggu/.htaccess
sudo chown -R www-data:www-data /var/www/html/lpd-canggu
```

---

### ❌ API berfungsi tapi halaman utama 404 (sub-folder XAMPP)

Edit `.htaccess` root — ubah `RewriteBase`:

```apache
# Dari:
RewriteBase /

# Menjadi (sesuai nama folder):
RewriteBase /lpd-canggu/
```

---

### ❌ `500 Internal Server Error` saat akses API

```bash
# Lihat log Apache
sudo tail -50 /var/log/apache2/error.log
sudo tail -50 /var/log/apache2/lpd-canggu-error.log

# Atau aktifkan display_errors sementara
sudo nano /var/www/html/lpd-canggu/.htaccess
# Tambahkan: php_flag display_errors On
```

---

### ❌ `Permission denied` pada folder `data/` atau `logs/`

```bash
sudo chown -R www-data:www-data /var/www/html/lpd-canggu/data
sudo chown -R www-data:www-data /var/www/html/lpd-canggu/logs
sudo chmod 755 /var/www/html/lpd-canggu/data /var/www/html/lpd-canggu/logs
```

---

### ❌ `.htaccess` diabaikan (AllowOverride None)

```bash
# Cek konfigurasi aktif
grep -r "AllowOverride" /etc/apache2/

# Edit konfigurasi site
sudo nano /etc/apache2/sites-available/lpd-canggu.conf
# Pastikan ada: AllowOverride All

sudo systemctl reload apache2
```

---

### ❌ `pdo_sqlite extension not found`

```bash
# Ubuntu/Debian — sesuaikan versi PHP
sudo apt-get install php8.4-sqlite3

# CentOS/RHEL
sudo dnf install php-sqlite3

# Restart Apache setelah install
sudo systemctl restart apache2

# Verifikasi
php -m | grep -i sqlite
```

---

### ❌ Cannot connect to SQL Server

```bash
# Test koneksi port SQL Server
nc -zv <DB_HOST> 1433

# Test PDO sqlsrv manual
php -r "
\$dsn = 'sqlsrv:Server=<DB_HOST>,1433;Database=<DB_DATABASE>;TrustServerCertificate=1';
try { new PDO(\$dsn, '<user>', '<pass>'); echo 'OK'; }
catch(PDOException \$e) { echo 'GAGAL: '.\$e->getMessage(); }
"
```

---

## 11. Keamanan Produksi

1. **Ganti password admin default** setelah instalasi:
   - Login → ubah password via UI, atau update langsung di DB:
     ```bash
     sqlite3 data/lpd_canggu.sqlite \
       "UPDATE gmob_nasabah SET pass_crypto='HASH_BARU' WHERE username='admin'"
     ```

2. **Aktifkan HTTPS** dengan Let's Encrypt:
   ```bash
   sudo apt-get install -y certbot python3-certbot-apache
   sudo certbot --apache -d lpd.contoh.id
   ```

3. **`.htaccess` sudah melindungi** folder sensitif (`data/`, `logs/`, `config/`, `includes/`, `api/`) — pastikan Apache membaca `.htaccess` (`AllowOverride All`)

4. **Nonaktifkan `/api/init-db`** setelah database selesai diinisialisasi:
   - Buka `router.php` → hapus atau komentari baris route `api/init-db`, atau
   - Tambahkan di `api/init.php` whitelist IP:
     ```php
     if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') json_err('Forbidden', '99', 403);
     ```

5. **Backup database SQLite** secara rutin:
   ```bash
   # Crontab — backup tiap malam jam 02:00
   0 2 * * * cp /var/www/html/lpd-canggu/data/lpd_canggu.sqlite \
     /backup/lpd_$(date +\%Y\%m\%d).sqlite
   ```

6. **Set `display_errors = Off`** di `.htaccess` production (sudah dikonfigurasi di `.htaccess` root)

---

## 📞 Informasi Repository

| Item | Detail |
|------|--------|
| **Repository** | https://github.com/pt-zenity/BPDBALI-TRF |
| **Branch** | `main` |
| **Bahasa** | PHP 7.0 — PHP 8.4 (tanpa framework) |
| **Web Server** | Apache 2.2/2.4 (direkomendasikan) · Nginx · PHP built-in |
| **Database** | SQLite (dev) / SQL Server MSSQL (produksi) |
| **Frontend** | HTML + Tailwind CSS CDN + Vanilla JS |
| **Dependensi** | Tidak ada (no Composer, no npm) |
| **Lisensi** | Proprietary — PT Zenity |
