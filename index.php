<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LPD Canggu — Sistem Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.0/css/all.min.css">
  <style>
    :root {
      --blue-dark: #1a3558;
      --blue-mid:  #2563a8;
      --blue-light:#3b82f6;
      --sidebar-w: 250px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #eef2f7; color: #1e293b; display: flex; height: 100vh; overflow: hidden; }

    /* ===== SIDEBAR ===== */
    .sidebar {
      width: var(--sidebar-w); min-width: var(--sidebar-w);
      background: linear-gradient(180deg, #1a3558 0%, #1e4480 100%);
      display: flex; flex-direction: column; height: 100vh; overflow-y: auto;
    }
    .sidebar-logo { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,.12); display: flex; align-items: center; gap: 12px; }
    .logo-icon { width: 42px; height: 42px; background: rgba(255,255,255,.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0; }
    .logo-text { color: #fff; font-weight: 700; font-size: 14px; line-height: 1.3; }
    .logo-sub  { color: #93c5fd; font-size: 11px; font-weight: 400; }
    nav { flex: 1; padding: 10px 8px; display: flex; flex-direction: column; gap: 1px; }
    .nav-group-title { color: #60a5fa; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 12px 12px 4px; }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 8px;
      color: #93c5fd; cursor: pointer; transition: all .15s;
      font-size: 13.5px; user-select: none; border: none; background: none; width: 100%; text-align: left;
    }
    .nav-item:hover  { background: rgba(255,255,255,.1); color: #fff; }
    .nav-item.active { background: rgba(255,255,255,.18); color: #fff; font-weight: 600; }
    .nav-item i { width: 18px; text-align: center; font-size: 13px; }
    .sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,.1); }
    .db-badge { font-size: 10px; padding: 2px 8px; border-radius: 12px; background: rgba(255,255,255,.12); color: #93c5fd; display: inline-block; margin-top: 4px; }

    /* ===== MAIN ===== */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar {
      background: #fff; padding: 14px 24px; border-bottom: 1px solid #e2e8f0;
      display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .page-content { flex: 1; overflow-y: auto; padding: 24px; }

    /* ===== CARDS ===== */
    .card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
    .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .card-title  { font-size: 15px; font-weight: 700; color: #1e293b; }
    .stat-card { border-radius: 14px; padding: 20px; color: #fff; position: relative; overflow: hidden; }
    .stat-card::after { content: ''; position: absolute; right: -15px; top: -15px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,.1); }

    /* ===== BUTTONS ===== */
    .btn {
      padding: 9px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px;
      cursor: pointer; border: none; transition: all .15s;
      display: inline-flex; align-items: center; gap: 7px; white-space: nowrap;
    }
    .btn-primary { background: #1a3558; color: #fff; } .btn-primary:hover { background: #0f2340; }
    .btn-success { background: #16a34a; color: #fff; } .btn-success:hover { background: #15803d; }
    .btn-danger  { background: #dc2626; color: #fff; } .btn-danger:hover  { background: #b91c1c; }
    .btn-warning { background: #d97706; color: #fff; } .btn-warning:hover { background: #b45309; }
    .btn-info    { background: #0891b2; color: #fff; } .btn-info:hover    { background: #0e7490; }
    .btn-outline { background: transparent; border: 2px solid #1a3558; color: #1a3558; }
    .btn-outline:hover { background: #1a3558; color: #fff; }
    .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 6px; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    /* ===== FORM ===== */
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 12.5px; font-weight: 600; color: #475569; }
    input, select, textarea {
      border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 9px 13px;
      font-size: 13.5px; outline: none; transition: border .15s; width: 100%;
      background: #fff; color: #1e293b; font-family: inherit;
    }
    input:focus, select:focus, textarea:focus { border-color: #2563a8; box-shadow: 0 0 0 3px rgba(37,99,168,.1); }
    input[readonly] { background: #f8fafc; color: #64748b; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }

    /* ===== TABLE ===== */
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #f1f5f9; padding: 10px 14px; text-align: left; font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .5px; white-space: nowrap; }
    tbody td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
    tbody tr:hover td { background: #f8fafc; }
    tbody tr:last-child td { border-bottom: none; }

    /* ===== BADGES ===== */
    .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
    .badge-green  { background: #dcfce7; color: #15803d; }
    .badge-yellow { background: #fef9c3; color: #a16207; }
    .badge-red    { background: #fee2e2; color: #b91c1c; }
    .badge-gray   { background: #f1f5f9; color: #64748b; }
    .badge-blue   { background: #dbeafe; color: #1d4ed8; }
    .badge-purple { background: #ede9fe; color: #6d28d9; }

    /* ===== SECTIONS ===== */
    .section { display: none; animation: fadeIn .2s ease; }
    .section.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

    /* ===== MODAL ===== */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .modal-overlay.hidden { display: none; }
    .modal-box { background: #fff; border-radius: 16px; padding: 26px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
    .modal-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 18px; }

    /* ===== TOAST ===== */
    #toast-wrap { position: fixed; top: 18px; right: 18px; z-index: 999; display: flex; flex-direction: column; gap: 8px; max-width: 360px; }
    .toast {
      background: #fff; border-radius: 10px; padding: 13px 16px;
      box-shadow: 0 6px 24px rgba(0,0,0,.15);
      display: flex; align-items: flex-start; gap: 11px;
      border-left: 4px solid; animation: slideIn .25s ease; font-size: 13.5px;
    }
    .toast.success { border-color: #16a34a; }
    .toast.error   { border-color: #dc2626; }
    .toast.info    { border-color: #0891b2; }
    .toast.warning { border-color: #d97706; }
    .toast-icon { font-size: 16px; margin-top: 1px; flex-shrink: 0; }
    .toast.success .toast-icon { color: #16a34a; }
    .toast.error   .toast-icon { color: #dc2626; }
    .toast.info    .toast-icon { color: #0891b2; }
    .toast.warning .toast-icon { color: #d97706; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: none; } }

    /* ===== MISC ===== */
    .divider { height: 1px; background: #e2e8f0; margin: 16px 0; }
    .text-muted { color: #94a3b8; font-size: 12.5px; }
    .fw-bold { font-weight: 700; }
    .text-green  { color: #16a34a; }
    .text-red    { color: #dc2626; }
    .text-blue   { color: #2563eb; }
    .saldo-big   { font-size: 28px; font-weight: 800; color: #1a3558; letter-spacing: -.5px; }
    .info-row    { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
    .info-row:last-child { border-bottom: none; }
    .info-label  { color: #64748b; }
    .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { text-align: center; padding: 40px 16px; color: #94a3b8; }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }
  </style>
</head>
<body>
<!-- ======= SIDEBAR ======= -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"><i class="fas fa-landmark"></i></div>
    <div>
      <div class="logo-text">LPD Canggu</div>
      <div class="logo-sub">Sistem Transaksi</div>
    </div>
  </div>
  <nav>
    <div class="nav-group-title">Utama</div>
    <button class="nav-item active" onclick="showSection('dashboard')">
      <i class="fas fa-chart-pie"></i> Dashboard
    </button>

    <div class="nav-group-title">Nasabah</div>
    <button class="nav-item" onclick="showSection('nasabah')">
      <i class="fas fa-users"></i> Data Nasabah
    </button>
    <button class="nav-item" onclick="showSection('tambah-nasabah')">
      <i class="fas fa-user-plus"></i> Tambah Nasabah
    </button>

    <div class="nav-group-title">Transaksi</div>
    <button class="nav-item" onclick="showSection('setor')">
      <i class="fas fa-arrow-down-to-line"></i> Setor Tunai
    </button>
    <button class="nav-item" onclick="showSection('tarik')">
      <i class="fas fa-arrow-up-from-line"></i> Tarik Tunai
    </button>
    <button class="nav-item" onclick="showSection('transfer-lpd')">
      <i class="fas fa-right-left"></i> Transfer LPD
    </button>
    <button class="nav-item" onclick="showSection('transfer-bank')">
      <i class="fas fa-building-columns"></i> Transfer Bank
    </button>

    <div class="nav-group-title">Laporan</div>
    <button class="nav-item" onclick="showSection('mutasi')">
      <i class="fas fa-list-alt"></i> Mutasi Rekening
    </button>
    <button class="nav-item" onclick="showSection('riwayat-transfer')">
      <i class="fas fa-clock-rotate-left"></i> Riwayat Transfer
    </button>

    <div class="nav-group-title">Sistem</div>
    <button class="nav-item" onclick="showSection('pengaturan')">
      <i class="fas fa-gear"></i> Pengaturan DB
    </button>
  </nav>
  <div class="sidebar-footer">
    <div style="color:#93c5fd;font-size:11px;">Database</div>
    <div class="db-badge" id="db-badge">SQLite</div>
  </div>
</aside>

<!-- ======= MAIN ======= -->
<div class="main">
  <!-- Topbar -->
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <div style="font-size:16px;font-weight:700;color:#1a3558;" id="page-title">Dashboard</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <span style="font-size:12.5px;color:#64748b;" id="clock-display"></span>
      <button class="btn btn-warning btn-sm" onclick="initDB()">
        <i class="fas fa-database"></i> Init DB
      </button>
    </div>
  </header>

  <!-- Content -->
  <main class="page-content">

    <!-- ======= DASHBOARD ======= -->
    <section id="section-dashboard" class="section active">
      <div class="grid-4 mb-4" id="stat-cards">
        <!-- Diisi JS -->
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-receipt mr-2"></i>Transaksi Terbaru</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Rekening</th><th>Nama</th><th>Jenis</th><th>Jumlah</th><th>Waktu</th></tr></thead>
              <tbody id="dash-trans-list"><tr><td colspan="5" class="empty-state"><i class="fas fa-inbox"></i>Belum ada data</td></tr></tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-header">
            <div class="card-title"><i class="fas fa-user-plus mr-2"></i>Nasabah Terbaru</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Nama</th><th>No Rek</th><th>Status</th><th>Tgl Daftar</th></tr></thead>
              <tbody id="dash-nasabah-list"><tr><td colspan="4" class="empty-state"><i class="fas fa-inbox"></i>Belum ada data</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- ======= DATA NASABAH ======= -->
    <section id="section-nasabah" class="section">
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-users mr-2"></i>Daftar Nasabah</div>
          <div style="display:flex;gap:8px;">
            <input type="text" id="search-nasabah" placeholder="Cari nama/username/norek..." style="width:220px;" oninput="debounceSearch(this.value)">
            <select id="filter-status" onchange="loadNasabah()" style="width:120px;">
              <option value="">Semua Status</option>
              <option value="A">Aktif</option>
              <option value="R">Registrasi</option>
              <option value="B">Blokir</option>
              <option value="T">Tutup</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="loadNasabah()"><i class="fas fa-sync"></i></button>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th><th>NOID</th><th>Nama</th><th>No Rekening</th>
                <th>Username</th><th>Telepon</th><th>Status</th>
                <th>Tgl Daftar</th><th>Aksi</th>
              </tr>
            </thead>
            <tbody id="nasabah-list"><tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i>Klik Refresh untuk memuat data</td></tr></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ======= TAMBAH NASABAH ======= -->
    <section id="section-tambah-nasabah" class="section">
      <div class="card" style="max-width:580px;margin:auto;">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-user-plus mr-2"></i>Tambah Nasabah Baru</div>
        </div>
        <form id="form-nasabah" onsubmit="submitNasabah(event)">
          <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="form-group">
              <label class="form-label">Nama Lengkap *</label>
              <input type="text" id="n-nama" placeholder="Contoh: I Wayan Sudarsana" required>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" id="n-username" placeholder="Contoh: wayan.sudarsana" required>
              </div>
              <div class="form-group">
                <label class="form-label">Telepon</label>
                <input type="tel" id="n-phone" placeholder="08xxxxxxxxxx">
              </div>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" id="n-pass" placeholder="Min. 6 karakter" required minlength="6">
              </div>
              <div class="form-group">
                <label class="form-label">PIN (6 digit)</label>
                <input type="text" id="n-pin" placeholder="123456" maxlength="6" value="123456">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" id="n-email" placeholder="contoh@email.com">
            </div>
            <div class="form-group">
              <label class="form-label">Alamat</label>
              <textarea id="n-alamat" rows="2" placeholder="Alamat lengkap"></textarea>
            </div>
            <div style="background:#dbeafe;border-radius:8px;padding:12px 14px;font-size:13px;color:#1d4ed8;">
              <i class="fas fa-info-circle"></i>
              <strong>Info:</strong> Nomor ID (NOID) dan Nomor Rekening akan dibuat otomatis. Status awal: <b>Registrasi</b> — aktifkan setelah didaftarkan.
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
              <button type="button" class="btn btn-outline" onclick="document.getElementById('form-nasabah').reset()">
                <i class="fas fa-redo"></i> Reset
              </button>
              <button type="submit" class="btn btn-success" id="btn-submit-nasabah">
                <i class="fas fa-save"></i> Daftarkan Nasabah
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>

    <!-- ======= SETOR TUNAI ======= -->
    <section id="section-setor" class="section">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="card">
          <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-arrow-down-to-line mr-2"></i>Setor Tunai</div>
          <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="form-group">
              <label class="form-label">No Rekening *</label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="setor-norek" placeholder="Contoh: 01.000001" oninput="debounceRek('setor')">
                <button class="btn btn-info btn-sm" onclick="cekRekening('setor')"><i class="fas fa-search"></i></button>
              </div>
            </div>
            <div id="setor-info" class="hidden"></div>
            <div class="form-group">
              <label class="form-label">Jumlah Setoran (Rp) *</label>
              <input type="number" id="setor-amount" placeholder="Contoh: 500000" min="1000" step="1000">
            </div>
            <div class="form-group">
              <label class="form-label">Keterangan</label>
              <input type="text" id="setor-ket" placeholder="Setor tunai" value="Setor tunai">
            </div>
            <button class="btn btn-success" onclick="submitSetor()">
              <i class="fas fa-check-circle"></i> Proses Setoran
            </button>
          </div>
        </div>
        <div class="card" id="setor-result" style="display:none;">
          <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-receipt mr-2"></i>Bukti Setoran</div>
          <div id="setor-result-content"></div>
        </div>
      </div>
    </section>

    <!-- ======= TARIK TUNAI ======= -->
    <section id="section-tarik" class="section">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="card">
          <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-arrow-up-from-line mr-2"></i>Tarik Tunai</div>
          <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="form-group">
              <label class="form-label">No Rekening *</label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="tarik-norek" placeholder="Contoh: 01.000001" oninput="debounceRek('tarik')">
                <button class="btn btn-info btn-sm" onclick="cekRekening('tarik')"><i class="fas fa-search"></i></button>
              </div>
            </div>
            <div id="tarik-info" class="hidden"></div>
            <div class="form-group">
              <label class="form-label">Jumlah Penarikan (Rp) *</label>
              <input type="number" id="tarik-amount" placeholder="Contoh: 200000" min="10000" step="1000">
            </div>
            <div class="form-group">
              <label class="form-label">Keterangan</label>
              <input type="text" id="tarik-ket" placeholder="Tarik tunai" value="Tarik tunai">
            </div>
            <div style="background:#fef9c3;border-radius:8px;padding:10px 14px;font-size:13px;color:#a16207;">
              <i class="fas fa-exclamation-triangle"></i> Minimum saldo mengendap: <b>Rp 50.000</b>
            </div>
            <button class="btn btn-warning" onclick="submitTarik()">
              <i class="fas fa-check-circle"></i> Proses Penarikan
            </button>
          </div>
        </div>
        <div class="card" id="tarik-result" style="display:none;">
          <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-receipt mr-2"></i>Bukti Penarikan</div>
          <div id="tarik-result-content"></div>
        </div>
      </div>
    </section>

    <!-- ======= TRANSFER LPD ======= -->
    <section id="section-transfer-lpd" class="section">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="card">
          <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-right-left mr-2"></i>Transfer Antar Rekening LPD</div>
          <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="form-group">
              <label class="form-label">Rekening Pengirim *</label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="lpd-from" placeholder="Contoh: 01.000001" oninput="debounceRek('lpd-from')">
                <button class="btn btn-info btn-sm" onclick="cekRekening('lpd-from')"><i class="fas fa-search"></i></button>
              </div>
            </div>
            <div id="lpd-from-info" class="hidden"></div>
            <div class="form-group">
              <label class="form-label">Rekening Tujuan *</label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="lpd-to" placeholder="Contoh: 01.000002" oninput="debounceRek('lpd-to')">
                <button class="btn btn-info btn-sm" onclick="cekRekening('lpd-to')"><i class="fas fa-search"></i></button>
              </div>
            </div>
            <div id="lpd-to-info" class="hidden"></div>
            <div class="form-group">
              <label class="form-label">Nominal Transfer (Rp) *</label>
              <input type="number" id="lpd-amount" placeholder="Min Rp 10.000 — Max Rp 5.000.000" min="10000" max="5000000" step="1000">
            </div>
            <div class="form-group">
              <label class="form-label">Keterangan</label>
              <input type="text" id="lpd-ket" placeholder="Transfer antar rekening LPD" value="Transfer LPD">
            </div>
            <div style="background:#dcfce7;border-radius:8px;padding:10px 14px;font-size:13px;color:#15803d;">
              <i class="fas fa-info-circle"></i> Transfer antar rekening LPD <b>GRATIS</b> biaya admin
            </div>
            <button class="btn btn-primary" onclick="submitTransferLPD()">
              <i class="fas fa-paper-plane"></i> Proses Transfer
            </button>
          </div>
        </div>
        <div class="card" id="lpd-result" style="display:none;">
          <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-receipt mr-2"></i>Bukti Transfer</div>
          <div id="lpd-result-content"></div>
        </div>
      </div>
    </section>

    <!-- ======= TRANSFER BANK ======= -->
    <section id="section-transfer-bank" class="section">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
        <div class="card">
          <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-building-columns mr-2"></i>Transfer ke Bank Lain</div>
          <div style="display:flex;flex-direction:column;gap:14px;">
            <div class="form-group">
              <label class="form-label">Rekening Pengirim *</label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="bank-from" placeholder="No rekening LPD" oninput="debounceRek('bank-from')">
                <button class="btn btn-info btn-sm" onclick="cekRekening('bank-from')"><i class="fas fa-search"></i></button>
              </div>
            </div>
            <div id="bank-from-info" class="hidden"></div>
            <div class="form-group">
              <label class="form-label">Bank Tujuan *</label>
              <select id="bank-code" onchange="updateBiayaAdmin()">
                <option value="">-- Pilih Bank --</option>
              </select>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">No Rekening Bank *</label>
                <input type="text" id="bank-acc" placeholder="No rekening tujuan">
              </div>
              <div class="form-group">
                <label class="form-label">Nama Penerima</label>
                <input type="text" id="bank-to-name" placeholder="Nama pemilik rekening">
              </div>
            </div>
            <div class="grid-2">
              <div class="form-group">
                <label class="form-label">Nominal Transfer (Rp) *</label>
                <input type="number" id="bank-amount" placeholder="Min Rp 10.000" min="10000" oninput="updateTotalBank()">
              </div>
              <div class="form-group">
                <label class="form-label">Biaya Admin</label>
                <input type="text" id="bank-biaya" readonly value="-">
              </div>
            </div>
            <div style="background:#f1f5f9;border-radius:8px;padding:10px 14px;">
              <div style="font-size:12.5px;color:#64748b;">Total Debit dari Rekening:</div>
              <div style="font-size:18px;font-weight:700;color:#1a3558;" id="bank-total-label">-</div>
            </div>
            <div class="form-group">
              <label class="form-label">Keterangan</label>
              <input type="text" id="bank-ket" value="Transfer ke bank lain">
            </div>
            <button class="btn btn-primary" onclick="submitTransferBank()">
              <i class="fas fa-paper-plane"></i> Proses Transfer
            </button>
          </div>
        </div>
        <div class="card" id="bank-result" style="display:none;">
          <div class="card-title" style="margin-bottom:14px;"><i class="fas fa-receipt mr-2"></i>Bukti Transfer Bank</div>
          <div id="bank-result-content"></div>
        </div>
      </div>
    </section>

    <!-- ======= MUTASI REKENING ======= -->
    <section id="section-mutasi" class="section">
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-list-alt mr-2"></i>Mutasi Rekening</div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end;">
          <div class="form-group" style="flex:0 0 200px;">
            <label class="form-label">No Rekening *</label>
            <input type="text" id="mutasi-norek" placeholder="Contoh: 01.000001">
          </div>
          <div class="form-group" style="flex:0 0 150px;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" id="mutasi-start">
          </div>
          <div class="form-group" style="flex:0 0 150px;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" id="mutasi-end">
          </div>
          <div class="form-group" style="flex:0 0 130px;">
            <label class="form-label">Tampilkan</label>
            <input type="number" id="mutasi-limit" value="50" min="5" max="200">
          </div>
          <button class="btn btn-primary" onclick="loadMutasi()"><i class="fas fa-search"></i> Lihat Mutasi</button>
        </div>
        <div id="mutasi-header" class="hidden" style="background:#f8fafc;border-radius:10px;padding:14px 18px;margin-bottom:14px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div class="fw-bold" id="mutasi-nama">-</div>
              <div class="text-muted" id="mutasi-norek-label">-</div>
            </div>
            <div style="text-align:right;">
              <div class="text-muted">Saldo</div>
              <div class="saldo-big" id="mutasi-saldo">-</div>
            </div>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>No</th><th>Tanggal</th><th>Kode</th><th>Keterangan</th><th>Debit</th><th>Kredit</th><th>Saldo</th></tr>
            </thead>
            <tbody id="mutasi-list"><tr><td colspan="7" class="empty-state"><i class="fas fa-inbox"></i>Masukkan no rekening dan klik "Lihat Mutasi"</td></tr></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ======= RIWAYAT TRANSFER ======= -->
    <section id="section-riwayat-transfer" class="section">
      <div class="card">
        <div class="card-header">
          <div class="card-title"><i class="fas fa-clock-rotate-left mr-2"></i>Riwayat Transfer</div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;align-items:flex-end;">
          <div class="form-group" style="flex:0 0 200px;">
            <label class="form-label">No Rekening *</label>
            <input type="text" id="riwayat-norek" placeholder="Contoh: 01.000001">
          </div>
          <div class="form-group" style="flex:0 0 130px;">
            <label class="form-label">Jenis</label>
            <select id="riwayat-jenis">
              <option value="">Semua</option>
              <option value="LPD">Transfer LPD</option>
              <option value="BANK">Transfer Bank</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="loadRiwayatTransfer()"><i class="fas fa-search"></i> Cari</button>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Dari</th><th>Ke</th><th>Nominal</th><th>Biaya</th><th>Keterangan</th><th>Trans No</th></tr>
            </thead>
            <tbody id="riwayat-list"><tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i>Masukkan no rekening dan klik "Cari"</td></tr></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ======= PENGATURAN DB ======= -->
    <section id="section-pengaturan" class="section">
      <div class="card" style="max-width:500px;margin:auto;">
        <div class="card-title" style="margin-bottom:16px;"><i class="fas fa-database mr-2"></i>Pengaturan Database</div>
        <div id="db-info-content" style="margin-bottom:20px;"></div>
        <div class="divider"></div>
        <div style="display:flex;flex-direction:column;gap:10px;">
          <button class="btn btn-primary" onclick="initDB()">
            <i class="fas fa-play-circle"></i> Init / Reset Database
          </button>
          <div style="font-size:12.5px;color:#94a3b8;">
            Akan membuat tabel-tabel yang diperlukan dan mengisi data awal (bank, admin).
            <br>Data yang sudah ada <b>tidak</b> akan dihapus (INSERT OR IGNORE).
          </div>
        </div>
      </div>
    </section>

  </main>
</div>

<!-- ======= MODAL DETAIL NASABAH ======= -->
<div class="modal-overlay hidden" id="modal-detail">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <div class="modal-title">Detail Nasabah</div>
      <button onclick="closeModal('modal-detail')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
    </div>
    <div id="modal-detail-content">Loading...</div>
  </div>
</div>

<!-- ======= MODAL KONFIRMASI AKTIFKAN ======= -->
<div class="modal-overlay hidden" id="modal-status">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <div class="modal-title" id="modal-status-title">Ubah Status Nasabah</div>
      <button onclick="closeModal('modal-status')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b;">✕</button>
    </div>
    <div id="modal-status-body">
      <p style="margin-bottom:14px;color:#475569;font-size:13.5px;" id="modal-status-msg"></p>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button class="btn btn-outline" onclick="closeModal('modal-status')">Batal</button>
        <button class="btn btn-success" id="btn-confirm-status" onclick="confirmStatus()">Konfirmasi</button>
      </div>
    </div>
    <input type="hidden" id="modal-status-id">
    <input type="hidden" id="modal-status-val">
  </div>
</div>

<!-- ======= TOAST ======= -->
<div id="toast-wrap"></div>

<script>
// ========================= CONFIG =========================
const BASE = '';
let _searchTimer = null;
let _rekTimer    = {};

// ========================= UTILITY =========================
async function api(method, path, body = null) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json' },
  };
  if (body && method !== 'GET') opts.body = JSON.stringify(body);
  const r = await fetch(BASE + path, opts);
  const text = await r.text();
  try { return JSON.parse(text); } catch { return { status: '99', message: text }; }
}

function toast(msg, type = 'info', dur = 3500) {
  const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
  const wrap = document.getElementById('toast-wrap');
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<i class="fas ${icons[type] || icons.info} toast-icon"></i><div>${msg}</div>`;
  wrap.appendChild(el);
  setTimeout(() => el.style.opacity = '0', dur - 400);
  setTimeout(() => el.remove(), dur);
}

function loading(btn, show) {
  if (!btn) return;
  if (show) {
    btn.dataset.orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> Proses...`;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.orig || btn.innerHTML;
  }
}

function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function rp(n) {
  return 'Rp ' + parseInt(n || 0).toLocaleString('id-ID');
}

function statusBadge(s) {
  const map = {
    'A': ['badge-green',  'Aktif'],
    'R': ['badge-yellow', 'Registrasi'],
    'B': ['badge-red',    'Blokir'],
    'T': ['badge-gray',   'Tutup'],
  };
  const [cls, label] = map[s] || ['badge-gray', s || '-'];
  return `<span class="badge ${cls}">${label}</span>`;
}

function receiptRow(label, val, bold = false) {
  return `<div class="info-row"><span class="info-label">${label}</span><span ${bold ? 'class="fw-bold"' : ''}>${val}</span></div>`;
}

// ========================= NAVIGATION =========================
const sectionTitles = {
  'dashboard'        : 'Dashboard',
  'nasabah'          : 'Data Nasabah',
  'tambah-nasabah'   : 'Tambah Nasabah',
  'setor'            : 'Setor Tunai',
  'tarik'            : 'Tarik Tunai',
  'transfer-lpd'     : 'Transfer LPD',
  'transfer-bank'    : 'Transfer ke Bank Lain',
  'mutasi'           : 'Mutasi Rekening',
  'riwayat-transfer' : 'Riwayat Transfer',
  'pengaturan'       : 'Pengaturan Database',
};

function showSection(name) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

  const sec = document.getElementById('section-' + name);
  if (sec) sec.classList.add('active');

  document.querySelectorAll('.nav-item').forEach(btn => {
    if (btn.getAttribute('onclick')?.includes(`'${name}'`)) btn.classList.add('active');
  });

  document.getElementById('page-title').textContent = sectionTitles[name] || name;

  // Auto-load
  if (name === 'dashboard')    loadDashboard();
  if (name === 'nasabah')      loadNasabah();
  if (name === 'transfer-bank') loadBankList();
  if (name === 'pengaturan')   loadDBInfo();
}

// ========================= CLOCK =========================
function updateClock() {
  const now = new Date();
  document.getElementById('clock-display').textContent =
    now.toLocaleDateString('id-ID', { weekday:'short', day:'2-digit', month:'short', year:'numeric' }) +
    ' ' + now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();

// ========================= INIT DB =========================
async function initDB() {
  const btn = event.target.closest('button');
  loading(btn, true);
  const res = await api('POST', '/api/init-db');
  loading(btn, false);
  if (res.status === '00') {
    toast('Database berhasil diinisialisasi — Driver: ' + (res.driver || '-'), 'success');
    document.getElementById('db-badge').textContent = (res.driver || 'SQLite').toUpperCase();
    if (document.getElementById('section-dashboard').classList.contains('active')) loadDashboard();
  } else {
    toast('Gagal init DB: ' + res.message, 'error');
  }
}

// ========================= DASHBOARD =========================
async function loadDashboard() {
  const res = await api('GET', '/api/dashboard');
  if (res.status !== '00') { toast('Gagal muat dashboard', 'error'); return; }

  const s = res.stats;
  document.getElementById('stat-cards').innerHTML = `
    <div class="stat-card" style="background:linear-gradient(135deg,#1a3558,#2563a8)">
      <div style="font-size:11px;opacity:.8;margin-bottom:6px;">TOTAL NASABAH</div>
      <div style="font-size:28px;font-weight:800;">${s.total_nasabah}</div>
      <div style="font-size:12px;opacity:.7;margin-top:4px;">Aktif: ${s.aktif_nasabah}</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#16a34a,#15803d)">
      <div style="font-size:11px;opacity:.8;margin-bottom:6px;">TOTAL SALDO</div>
      <div style="font-size:22px;font-weight:800;">${s.total_saldo_fmt}</div>
      <div style="font-size:12px;opacity:.7;margin-top:4px;">Dari ${s.total_nasabah} nasabah</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
      <div style="font-size:11px;opacity:.8;margin-bottom:6px;">TRANSAKSI HARI INI</div>
      <div style="font-size:28px;font-weight:800;">${s.trans_hari_ini}</div>
      <div style="font-size:12px;opacity:.7;margin-top:4px;">Total: ${s.total_trans}</div>
    </div>
    <div class="stat-card" style="background:linear-gradient(135deg,#d97706,#b45309)">
      <div style="font-size:11px;opacity:.8;margin-bottom:6px;">MUTASI HARI INI</div>
      <div style="font-size:14px;font-weight:700;">D: ${s.debit_fmt}</div>
      <div style="font-size:14px;font-weight:700;">K: ${s.kredit_fmt}</div>
    </div>
  `;

  // Recent transactions
  const tbody = document.getElementById('dash-trans-list');
  if (!res.recent_transactions?.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-inbox"></i>Belum ada transaksi</td></tr>';
  } else {
    tbody.innerHTML = res.recent_transactions.map(t => `
      <tr>
        <td><span class="text-muted">${t.linker}</span></td>
        <td>${t.nama || '-'}</td>
        <td><span class="badge ${t.jenis === 'Debit' ? 'badge-red' : 'badge-green'}">${t.jenis} ${t.trans_code}</span></td>
        <td class="${t.jenis === 'Debit' ? 'text-red' : 'text-green'} fw-bold">
          ${t.jenis === 'Debit' ? '-' : '+'}${rp(parseFloat(t.debit || 0) > 0 ? t.debit : t.credit)}
        </td>
        <td class="text-muted">${t.tgl_fmt || '-'}</td>
      </tr>
    `).join('');
  }

  // Recent nasabah
  const tbody2 = document.getElementById('dash-nasabah-list');
  if (!res.recent_nasabah?.length) {
    tbody2.innerHTML = '<tr><td colspan="4" class="empty-state"><i class="fas fa-inbox"></i>Belum ada nasabah</td></tr>';
  } else {
    tbody2.innerHTML = res.recent_nasabah.map(n => `
      <tr>
        <td>${n.nama}</td>
        <td><code>${n.norek}</code></td>
        <td>${statusBadge(n.status)}</td>
        <td class="text-muted">${n.created_at?.substring(0,10) || '-'}</td>
      </tr>
    `).join('');
  }
}

// ========================= NASABAH =========================
let _searchQ = '';
function debounceSearch(val) {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(() => { _searchQ = val; loadNasabah(); }, 400);
}

async function loadNasabah() {
  const q      = document.getElementById('search-nasabah')?.value || _searchQ || '';
  const status = document.getElementById('filter-status')?.value || '';
  const tbody  = document.getElementById('nasabah-list');
  tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

  let url = '/api/nasabah?';
  if (q) url += 'q=' + encodeURIComponent(q) + '&';
  if (status) url += 'status=' + status;

  const res = await api('GET', url);
  if (res.status !== '00') { toast('Gagal muat nasabah', 'error'); return; }

  if (!res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i>Tidak ada data nasabah</td></tr>';
    return;
  }

  tbody.innerHTML = res.data.map((n, i) => `
    <tr>
      <td class="text-muted">${i + 1}</td>
      <td><code style="font-size:12px;">${n.noid}</code></td>
      <td class="fw-bold">${n.nama}</td>
      <td><code>${n.norek}</code></td>
      <td>${n.username}</td>
      <td>${n.phone || '-'}</td>
      <td>${statusBadge(n.status)}</td>
      <td class="text-muted">${n.created_at?.substring(0,10) || '-'}</td>
      <td>
        <div style="display:flex;gap:4px;">
          <button class="btn btn-info btn-sm" onclick="detailNasabah(${n.id})" title="Detail"><i class="fas fa-eye"></i></button>
          ${n.status !== 'A' ? `<button class="btn btn-success btn-sm" onclick="ubahStatus(${n.id},'${n.nama}','A')" title="Aktifkan"><i class="fas fa-check"></i></button>` : ''}
          ${n.status === 'A' ? `<button class="btn btn-warning btn-sm" onclick="ubahStatus(${n.id},'${n.nama}','B')" title="Blokir"><i class="fas fa-ban"></i></button>` : ''}
          <button class="btn btn-danger btn-sm" onclick="ubahStatus(${n.id},'${n.nama}','T')" title="Tutup"><i class="fas fa-times"></i></button>
        </div>
      </td>
    </tr>
  `).join('');
}

async function detailNasabah(id) {
  openModal('modal-detail');
  document.getElementById('modal-detail-content').innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';

  const res = await api('GET', `/api/nasabah/${id}`);
  if (res.status !== '00') {
    document.getElementById('modal-detail-content').innerHTML = '<div style="color:red;">Gagal memuat data</div>';
    return;
  }
  const d = res.data;
  document.getElementById('modal-detail-content').innerHTML = `
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
      <div style="width:52px;height:52px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;color:#2563eb;flex-shrink:0;">
        <i class="fas fa-user"></i>
      </div>
      <div>
        <div class="fw-bold" style="font-size:16px;">${d.nama}</div>
        <div class="text-muted">${d.noid}</div>
        <div style="margin-top:4px;">${statusBadge(d.status)}</div>
      </div>
    </div>
    ${receiptRow('No Rekening', `<code>${d.norek}</code>`)}
    ${receiptRow('Username',    d.username)}
    ${receiptRow('Telepon',     d.phone || '-')}
    ${receiptRow('Email',       d.email || '-')}
    ${receiptRow('Saldo',       `<span class="fw-bold text-green">${rp(d.saldo)}</span>`, false)}
    ${receiptRow('Produk',      d.produk || 'Tabungan')}
    ${receiptRow('Tgl Daftar',  d.created_at?.substring(0,19) || '-')}
    ${d.histori?.length ? `
      <div style="margin-top:14px;">
        <div class="fw-bold" style="margin-bottom:8px;font-size:13px;">5 Transaksi Terakhir</div>
        <table style="width:100%;font-size:12.5px;">
          <thead><tr>
            <th style="padding:6px 8px;background:#f1f5f9;text-align:left;">Tgl</th>
            <th style="padding:6px 8px;background:#f1f5f9;text-align:left;">Ket</th>
            <th style="padding:6px 8px;background:#f1f5f9;text-align:right;">Debit</th>
            <th style="padding:6px 8px;background:#f1f5f9;text-align:right;">Kredit</th>
          </tr></thead>
          <tbody>
            ${d.histori.map(h => `<tr>
              <td style="padding:5px 8px;">${h.trans_date?.substring(0,16) || '-'}</td>
              <td style="padding:5px 8px;">${h.remark || '-'}</td>
              <td style="padding:5px 8px;text-align:right;color:#dc2626;">${parseFloat(h.debit) > 0 ? rp(h.debit) : '-'}</td>
              <td style="padding:5px 8px;text-align:right;color:#16a34a;">${parseFloat(h.credit) > 0 ? rp(h.credit) : '-'}</td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
    ` : ''}
    <div style="margin-top:16px;display:flex;gap:8px;justify-content:flex-end;">
      ${d.status !== 'A' ? `<button class="btn btn-success btn-sm" onclick="ubahStatus(${d.id},'${d.nama}','A');closeModal('modal-detail')">Aktifkan</button>` : ''}
      <button class="btn btn-outline btn-sm" onclick="closeModal('modal-detail')">Tutup</button>
    </div>
  `;
}

function ubahStatus(id, nama, status) {
  const labels = { A:'Aktifkan', B:'Blokir', T:'Tutup', R:'Reset ke Registrasi' };
  document.getElementById('modal-status-title').textContent = labels[status] + ' Nasabah';
  document.getElementById('modal-status-msg').textContent = `Yakin akan mengubah status nasabah "${nama}" menjadi "${status}"?`;
  document.getElementById('modal-status-id').value  = id;
  document.getElementById('modal-status-val').value = status;
  openModal('modal-status');
}

async function confirmStatus() {
  const id     = document.getElementById('modal-status-id').value;
  const status = document.getElementById('modal-status-val').value;
  const btn    = document.getElementById('btn-confirm-status');
  loading(btn, true);
  const res = await api('PUT', `/api/nasabah/${id}/status`, { status });
  loading(btn, false);
  closeModal('modal-status');
  if (res.status === '00') {
    toast(res.message, 'success');
    loadNasabah();
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

async function submitNasabah(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-submit-nasabah');
  loading(btn, true);

  const body = {
    nama:        document.getElementById('n-nama').value.trim(),
    username:    document.getElementById('n-username').value.trim(),
    pass_crypto: document.getElementById('n-pass').value,
    pin_crypto:  document.getElementById('n-pin').value  || '123456',
    phone:       document.getElementById('n-phone').value.trim(),
    email:       document.getElementById('n-email').value.trim(),
    alamat:      document.getElementById('n-alamat').value.trim(),
  };

  const res = await api('POST', '/api/nasabah', body);
  loading(btn, false);

  if (res.status === '00') {
    toast(`Nasabah ${res.nama} berhasil didaftarkan! NoRek: ${res.norek}`, 'success', 5000);
    document.getElementById('form-nasabah').reset();
    document.getElementById('n-pin').value = '123456';
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

// ========================= CEK REKENING =========================
function debounceRek(type) {
  clearTimeout(_rekTimer[type]);
  _rekTimer[type] = setTimeout(() => cekRekening(type), 500);
}

async function cekRekening(type) {
  const inputs = {
    'setor'     : 'setor-norek',
    'tarik'     : 'tarik-norek',
    'lpd-from'  : 'lpd-from',
    'lpd-to'    : 'lpd-to',
    'bank-from' : 'bank-from',
  };
  const infoIds = {
    'setor'    : 'setor-info',
    'tarik'    : 'tarik-info',
    'lpd-from' : 'lpd-from-info',
    'lpd-to'   : 'lpd-to-info',
    'bank-from': 'bank-from-info',
  };

  const norek   = document.getElementById(inputs[type])?.value?.trim();
  const infoEl  = document.getElementById(infoIds[type]);
  if (!norek || !infoEl) return;

  infoEl.className = '';
  infoEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari rekening...';

  const res = await api('GET', '/api/saldo/' + encodeURIComponent(norek));
  if (res.status !== '00') {
    infoEl.innerHTML = `<div style="background:#fee2e2;border-radius:8px;padding:10px 14px;color:#b91c1c;font-size:13px;"><i class="fas fa-times-circle"></i> ${res.message}</div>`;
    return;
  }
  const d = res;
  const aktif = d.status === 'A';
  infoEl.innerHTML = `
    <div style="background:${aktif ? '#dcfce7' : '#fef9c3'};border-radius:8px;padding:12px 14px;">
      <div style="font-weight:700;font-size:14px;">${d.nama}</div>
      <div style="font-size:12.5px;color:#64748b;">${d.norek} — ${d.produk}</div>
      <div style="font-size:18px;font-weight:800;color:#1a3558;margin-top:6px;">${d.saldo_fmt}</div>
      ${!aktif ? `<div style="color:#d97706;font-size:12px;margin-top:4px;"><i class="fas fa-exclamation-triangle"></i> Status rekening: ${d.status}</div>` : ''}
    </div>`;
}

// ========================= SETOR =========================
async function submitSetor() {
  const norek  = document.getElementById('setor-norek').value.trim();
  const amount = parseFloat(document.getElementById('setor-amount').value);
  const ket    = document.getElementById('setor-ket').value.trim();
  const btn    = event.target.closest('button');

  if (!norek)        { toast('No rekening wajib', 'warning'); return; }
  if (isNaN(amount) || amount <= 0) { toast('Jumlah setoran wajib', 'warning'); return; }

  loading(btn, true);
  const res = await api('POST', '/api/setor', { norek, amount, remark: ket });
  loading(btn, false);

  if (res.status === '00') {
    toast('Setor berhasil: ' + res.jumlah_fmt, 'success');
    document.getElementById('setor-result').style.display = 'block';
    document.getElementById('setor-result-content').innerHTML = `
      ${receiptRow('No Rekening', `<code>${res.norek}</code>`)}
      ${receiptRow('Nama',         res.nama)}
      ${receiptRow('Jumlah Setor', `<span class="fw-bold text-green">${res.jumlah_fmt}</span>`)}
      ${receiptRow('Saldo Akhir',  `<span class="fw-bold" style="font-size:16px;">${res.saldo_fmt}</span>`)}
      ${receiptRow('No Transaksi', `<code>${res.trans_no}</code>`)}
      ${receiptRow('Waktu',        new Date().toLocaleString('id-ID'))}
    `;
    cekRekening('setor');
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

// ========================= TARIK =========================
async function submitTarik() {
  const norek  = document.getElementById('tarik-norek').value.trim();
  const amount = parseFloat(document.getElementById('tarik-amount').value);
  const ket    = document.getElementById('tarik-ket').value.trim();
  const btn    = event.target.closest('button');

  if (!norek)        { toast('No rekening wajib', 'warning'); return; }
  if (isNaN(amount) || amount <= 0) { toast('Jumlah penarikan wajib', 'warning'); return; }

  loading(btn, true);
  const res = await api('POST', '/api/tarik', { norek, amount, remark: ket });
  loading(btn, false);

  if (res.status === '00') {
    toast('Tarik berhasil: ' + res.jumlah_fmt, 'success');
    document.getElementById('tarik-result').style.display = 'block';
    document.getElementById('tarik-result-content').innerHTML = `
      ${receiptRow('No Rekening', `<code>${res.norek}</code>`)}
      ${receiptRow('Nama',         res.nama)}
      ${receiptRow('Jumlah Tarik', `<span class="fw-bold text-red">${res.jumlah_fmt}</span>`)}
      ${receiptRow('Saldo Akhir',  `<span class="fw-bold" style="font-size:16px;">${res.saldo_fmt}</span>`)}
      ${receiptRow('No Transaksi', `<code>${res.trans_no}</code>`)}
      ${receiptRow('Waktu',        new Date().toLocaleString('id-ID'))}
    `;
    cekRekening('tarik');
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

// ========================= TRANSFER LPD =========================
async function submitTransferLPD() {
  const from   = document.getElementById('lpd-from').value.trim();
  const to     = document.getElementById('lpd-to').value.trim();
  const amount = parseFloat(document.getElementById('lpd-amount').value);
  const ket    = document.getElementById('lpd-ket').value.trim();
  const btn    = event.target.closest('button');

  if (!from)         { toast('Rekening pengirim wajib', 'warning'); return; }
  if (!to)           { toast('Rekening tujuan wajib',   'warning'); return; }
  if (from === to)   { toast('Rekening pengirim dan tujuan sama', 'warning'); return; }
  if (isNaN(amount) || amount <= 0) { toast('Nominal transfer wajib', 'warning'); return; }

  loading(btn, true);
  const res = await api('POST', '/api/transfer-lpd', { from_norek: from, to_norek: to, amount, remark: ket });
  loading(btn, false);

  if (res.status === '00') {
    toast('Transfer LPD berhasil!', 'success');
    document.getElementById('lpd-result').style.display = 'block';
    document.getElementById('lpd-result-content').innerHTML = `
      ${receiptRow('Dari Rekening',   `<code>${res.from_norek}</code> ${res.from_name}`)}
      ${receiptRow('Ke Rekening',     `<code>${res.to_norek}</code> ${res.to_name}`)}
      ${receiptRow('Nominal',         `<span class="fw-bold text-blue">${res.jumlah_fmt}</span>`)}
      ${receiptRow('Biaya Admin',     '<span class="text-green">Gratis</span>')}
      ${receiptRow('Saldo Pengirim',  `<span class="fw-bold">${res.saldo_fmt}</span>`)}
      ${receiptRow('No Transaksi',    `<code>${res.trans_no}</code>`)}
      ${receiptRow('Waktu',           new Date().toLocaleString('id-ID'))}
    `;
    cekRekening('lpd-from');
    cekRekening('lpd-to');
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

// ========================= TRANSFER BANK =========================
let _bankList = [];

async function loadBankList() {
  if (_bankList.length > 0) return;
  const res = await api('GET', '/api/bank-list');
  if (res.status !== '00') return;
  _bankList = res.data || [];

  const sel = document.getElementById('bank-code');
  sel.innerHTML = '<option value="">-- Pilih Bank --</option>' +
    _bankList.map(b => `<option value="${b.bank_code}" data-cost="${b.transfer_cost}">${b.bank_name} (${b.bank_code})</option>`).join('');
}

function updateBiayaAdmin() {
  const sel  = document.getElementById('bank-code');
  const opt  = sel.options[sel.selectedIndex];
  const cost = opt?.dataset?.cost ? parseFloat(opt.dataset.cost) : 0;
  document.getElementById('bank-biaya').value = cost > 0 ? rp(cost) : '-';
  updateTotalBank();
}

function updateTotalBank() {
  const amount = parseFloat(document.getElementById('bank-amount')?.value || 0);
  const sel    = document.getElementById('bank-code');
  const opt    = sel?.options[sel.selectedIndex];
  const cost   = opt?.dataset?.cost ? parseFloat(opt.dataset.cost) : 0;
  const total  = amount + cost;
  document.getElementById('bank-total-label').textContent = total > 0 ? rp(total) : '-';
}

async function submitTransferBank() {
  const from      = document.getElementById('bank-from').value.trim();
  const bankCode  = document.getElementById('bank-code').value;
  const bankAcc   = document.getElementById('bank-acc').value.trim();
  const toName    = document.getElementById('bank-to-name').value.trim();
  const amount    = parseFloat(document.getElementById('bank-amount').value);
  const ket       = document.getElementById('bank-ket').value.trim();
  const btn       = event.target.closest('button');

  if (!from)     { toast('Rekening pengirim wajib', 'warning'); return; }
  if (!bankCode) { toast('Pilih bank tujuan', 'warning'); return; }
  if (!bankAcc)  { toast('No rekening bank tujuan wajib', 'warning'); return; }
  if (isNaN(amount) || amount <= 0) { toast('Nominal transfer wajib', 'warning'); return; }

  loading(btn, true);
  const res = await api('POST', '/api/transfer-bank', {
    from_norek: from, bank_code: bankCode, bank_acc: bankAcc,
    to_name: toName, amount, remark: ket,
  });
  loading(btn, false);

  if (res.status === '00') {
    toast('Transfer ke bank berhasil!', 'success');
    document.getElementById('bank-result').style.display = 'block';
    document.getElementById('bank-result-content').innerHTML = `
      ${receiptRow('Dari Rekening', `<code>${res.from_norek}</code> ${res.from_name}`)}
      ${receiptRow('Bank Tujuan',   `${res.to_bank_name} (${res.to_bank_code})`)}
      ${receiptRow('No Rek Tujuan', `<code>${res.to_bank_acc}</code> ${res.to_name || ''}`)}
      ${receiptRow('Nominal',       `<span class="fw-bold">${res.jumlah_fmt}</span>`)}
      ${receiptRow('Biaya Admin',   `<span class="text-red">${res.biaya_fmt}</span>`)}
      ${receiptRow('Total Debit',   `<span class="fw-bold text-red">${res.total_fmt}</span>`)}
      ${receiptRow('Saldo Akhir',   `<span class="fw-bold">${res.saldo_fmt}</span>`)}
      ${receiptRow('No Transaksi',  `<code>${res.trans_no}</code>`)}
      ${receiptRow('Waktu',         new Date().toLocaleString('id-ID'))}
    `;
    cekRekening('bank-from');
  } else {
    toast('Gagal: ' + res.message, 'error');
  }
}

// ========================= MUTASI =========================
async function loadMutasi() {
  const norek = document.getElementById('mutasi-norek').value.trim();
  const start = document.getElementById('mutasi-start').value;
  const end   = document.getElementById('mutasi-end').value;
  const limit = document.getElementById('mutasi-limit').value || 50;

  if (!norek) { toast('No rekening wajib', 'warning'); return; }

  const tbody = document.getElementById('mutasi-list');
  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

  let url = `/api/mutasi/${encodeURIComponent(norek)}?limit=${limit}`;
  if (start) url += '&start_date=' + start;
  if (end)   url += '&end_date='   + end;

  const res = await api('GET', url);
  if (res.status !== '00') {
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state" style="color:#dc2626;">${res.message}</td></tr>`;
    return;
  }

  // Header
  document.getElementById('mutasi-header').classList.remove('hidden');
  document.getElementById('mutasi-nama').textContent         = res.nama;
  document.getElementById('mutasi-norek-label').textContent  = 'No Rekening: ' + res.norek;
  document.getElementById('mutasi-saldo').textContent        = res.saldo_fmt;

  if (!res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><i class="fas fa-inbox"></i>Tidak ada mutasi</td></tr>';
    return;
  }

  tbody.innerHTML = res.data.map((t, i) => `
    <tr>
      <td class="text-muted">${i + 1}</td>
      <td>${t.tgl_fmt || t.trans_date?.substring(0,19) || '-'}</td>
      <td><span class="badge badge-blue">${t.trans_code}</span></td>
      <td>${t.remark || '-'}</td>
      <td class="text-red fw-bold">${parseFloat(t.debit) > 0 ? rp(t.debit) : '-'}</td>
      <td class="text-green fw-bold">${parseFloat(t.credit) > 0 ? rp(t.credit) : '-'}</td>
      <td class="fw-bold">${rp(t.saldo)}</td>
    </tr>
  `).join('');
}

// ========================= RIWAYAT TRANSFER =========================
async function loadRiwayatTransfer() {
  const norek = document.getElementById('riwayat-norek').value.trim();
  const jenis = document.getElementById('riwayat-jenis').value;
  const tbody = document.getElementById('riwayat-list');

  if (!norek) { toast('No rekening wajib', 'warning'); return; }

  tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

  let url = `/api/riwayat-transfer/${encodeURIComponent(norek)}`;
  if (jenis) url += '?jenis=' + jenis;

  const res = await api('GET', url);
  if (res.status !== '00') {
    tbody.innerHTML = `<tr><td colspan="9" class="empty-state" style="color:#dc2626;">${res.message}</td></tr>`;
    return;
  }

  if (!res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="empty-state"><i class="fas fa-inbox"></i>Tidak ada riwayat transfer</td></tr>';
    return;
  }

  tbody.innerHTML = res.data.map((t, i) => `
    <tr>
      <td class="text-muted">${i + 1}</td>
      <td class="text-muted">${t.tgl_fmt || '-'}</td>
      <td><span class="badge ${t.jenis === 'LPD' ? 'badge-blue' : 'badge-purple'}">${t.jenis}</span>
          ${t.arah === 'keluar' ? '<span class="badge-red badge" style="margin-left:2px;">↑</span>' : '<span class="badge-green badge" style="margin-left:2px;">↓</span>'}</td>
      <td><code style="font-size:11.5px;">${t.from_norek}</code><br><span class="text-muted">${t.from_name || ''}</span></td>
      <td>
        ${t.jenis === 'BANK' ? `${t.bank_name || ''}<br><code style="font-size:11.5px;">${t.to_norek}</code>` : `<code style="font-size:11.5px;">${t.to_norek}</code><br><span class="text-muted">${t.to_name || ''}</span>`}
      </td>
      <td class="fw-bold text-blue">${t.amount_fmt}</td>
      <td>${parseFloat(t.cost) > 0 ? t.cost_fmt : '<span class="text-green">Gratis</span>'}</td>
      <td class="text-muted" style="font-size:12.5px;">${t.remark || '-'}</td>
      <td><code style="font-size:11px;">${t.trans_no || '-'}</code></td>
    </tr>
  `).join('');
}

// ========================= DB INFO =========================
async function loadDBInfo() {
  const res = await api('GET', '/api/dashboard');
  if (res.status !== '00') return;
  document.getElementById('db-info-content').innerHTML = `
    <div class="info-row"><span class="info-label">Driver</span><span class="fw-bold badge badge-blue">SQLite</span></div>
    <div class="info-row"><span class="info-label">Total Nasabah</span><span>${res.stats?.total_nasabah || 0}</span></div>
    <div class="info-row"><span class="info-label">Total Rekening</span><span>${res.stats?.total_nasabah || 0}</span></div>
    <div class="info-row"><span class="info-label">Total Transaksi</span><span>${res.stats?.total_trans || 0}</span></div>
    <div class="info-row"><span class="info-label">Total Saldo</span><span class="fw-bold text-green">${res.stats?.total_saldo_fmt || '-'}</span></div>
  `;
}

// ========================= INIT =========================
window.addEventListener('load', () => {
  // Auto init DB pertama kali
  fetch('/api/dashboard').then(r => r.json()).then(res => {
    if (res.status !== '00') {
      // DB belum diinit
      fetch('/api/init-db', { method: 'POST' })
        .then(() => loadDashboard())
        .catch(() => {});
    } else {
      loadDashboard();
    }
  }).catch(() => {});
});
</script>
</body>
</html>
