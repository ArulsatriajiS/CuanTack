<?php
session_start();
require 'fungsi.php';

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Jika tombol "Simpan" ditekan
if (isset($_POST['simpan_transaksi'])) {
    if (simpan_transaksi($_POST, $user_id) > 0) {
        echo "<script>alert('Transaksi berhasil disimpan!'); window.location='catat_transaksi.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan transaksi!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="catat_transaksi.css">
</head>
<body class="bg-main">

    <div class="d-flex vh-100 overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="sidebar-custom d-flex flex-column p-3">
            <div class="d-flex align-items-center mb-5 mt-2 px-2">
                <img src="../images/Logo_1.1.png" alt="Logo CuanTrack" style="height: 35px;" class="me-2">
                <span class="fs-4 fw-bold text-dark-custom">Cuan Track</span>
            </div>
            <ul class="nav nav-pills flex-column mb-auto gap-2">
                <li class="nav-item">
                    <a href="beranda.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-house-door-fill me-3 fs-5"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a href="budgeting.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-bullseye me-3 fs-5"></i> Budgeting
                    </a>
                </li>
                <li class="nav-item">
                    <a href="catat_transaksi.php" class="nav-link nav-link-custom active d-flex align-items-center">
                        <i class="bi bi-calendar2-plus-fill me-3 fs-5"></i> Catat Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="riwayat_transaksi.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-calculator me-3 fs-5"></i> Riwayat Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analisa.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill me-3 fs-5"></i> Analisa
                    </a>
                </li>
            </ul>

            <!-- Menu Bawah Sidebar -->
            <hr class="border-secondary opacity-25 mx-2">
            <ul class="nav nav-pills flex-column gap-2 mb-3">
                <li class="nav-item">
                    <a href="pengaturan.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-gear-fill me-3 fs-5"></i> Pengaturan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-box-arrow-right me-3 fs-5"></i> Keluar
                    </a>
                </li>
            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-grow-1 d-flex flex-column overflow-y-auto">
            
            <header class="d-flex justify-content-between align-items-center p-4 border-bottom bg-white">
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Catat Transaksi</h4>
                <div class="d-flex align-items-center pe-3">
                    <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark-custom lh-1"><?= $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                    </div>
                </div>
            </header>

            <!-- AREA GRID 2 KOLOM BERSEBELAHAN (KEDUA KARTU BIRU PASTEL) -->
            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                <div class="row g-4 items-stretch">
                    
                    <!-- KOLOM KIRI: KALENDER INTERAKTIF (WARNA BIRU) -->
                    <div class="col-lg-6">
                        <div class="card-transaksi-blue d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold text-dark-custom mb-0" id="bulanTahunTeks">Bulan Tahun</h5>
                                    <div>
                                        <button type="button" class="btn btn-sm bg-white border me-1 shadow-sm" id="btnPrevMonth" style="border-radius: 8px;"><i class="bi bi-chevron-left"></i></button>
                                        <button type="button" class="btn btn-sm bg-white border shadow-sm" id="btnNextMonth" style="border-radius: 8px;"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                                
                                <div class="calendar-grid mb-2">
                                    <div class="calendar-header">MIN</div><div class="calendar-header">SEN</div><div class="calendar-header">SEL</div>
                                    <div class="calendar-header">RAB</div><div class="calendar-header">KAM</div><div class="calendar-header">JUM</div><div class="calendar-header">SAB</div>
                                </div>
                                <div class="calendar-grid" id="kalenderAngka">
                                    <!-- Angka kalender akan dimuat otomatis oleh JavaScript -->
                                </div>
                            </div>
                            <!-- TEKS "Klik tanggal untuk memilih..." SUDAH DIHAPUS DARI SINI -->
                        </div>
                    </div>

                    <!-- KOLOM KANAN: FORM INPUT TRANSAKSI (WARNA BIRU) -->
                    <div class="col-lg-6">
                        <div class="card-transaksi-blue">
                            <h5 class="fw-bold text-dark-custom mb-4">Tambah Transaksi</h5>
                            
                            <form action="" method="POST">
                                <!-- TOGGLE PENGELUARAN / PEMASUKAN -->
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="jenis" id="jenis1" value="Pengeluaran" checked>
                                        <label class="btn btn-toggle-jenis w-100" for="jenis1">Pengeluaran</label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="jenis" id="jenis2" value="Pemasukan">
                                        <label class="btn btn-toggle-jenis w-100" for="jenis2">Pemasukan</label>
                                    </div>
                                </div>

                                <!-- INPUT TANGGAL (Otomatis Hari Ini & Readonly agar pakai kalender) -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark-custom mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" id="inputTanggal" class="form-control form-control-custom" required readonly>
                                </div>

                                <!-- INPUT JUMLAH -->
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-dark-custom mb-1">Jumlah</label>
                                    <input type="text" name="jumlah" id="inputJumlah" class="form-control form-control-custom" placeholder="Rp 0" required autocomplete="off">
                                </div>

                                <!-- PILIHAN KATEGORI (Otomatis Berubah oleh JS) -->
                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-dark-custom mb-2">Kategori</label>
                                    <!-- Tambahkan id="wadahKategori" di sini -->
                                    <div class="grid-kategori" id="wadahKategori">
                                        
                                        <!-- Default Pengeluaran (Akan diganti dinamis oleh JS saat Pemasukan diklik) -->
                                        <input type="radio" class="btn-check" name="kategori" id="kat1" value="Kebutuhan Pokok" checked>
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat1">
                                            <i class="bi bi-cart3 fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Kebutuhan Pokok</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="kategori" id="kat2" value="Transportasi">
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat2">
                                            <i class="bi bi-scooter fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Transportasi</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="kategori" id="kat3" value="Kesehatan">
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat3">
                                            <i class="bi bi-capsule fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Kesehatan</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="kategori" id="kat4" value="Hiburan">
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat4">
                                            <i class="bi bi-film fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Hiburan</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="kategori" id="kat5" value="Belanja">
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat5">
                                            <i class="bi bi-bag-heart fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Belanja</span>
                                        </label>

                                        <input type="radio" class="btn-check" name="kategori" id="kat6" value="Lainnya">
                                        <label class="btn btn-kategori-box d-flex flex-column align-items-center" for="kat6">
                                            <i class="bi bi-box-seam fs-4 mb-1"></i>
                                            <span style="font-size: 0.7rem;">Lainnya</span>
                                        </label>

                                    </div>
                                </div>

                                <!-- TOMBOL AKSI -->
                                <div class="row g-2">
                                    <div class="col-4">
                                        <button type="reset" class="btn bg-white border w-100 py-2 fw-bold shadow-sm" style="border-radius: 12px; color: #475569;">Batal</button>
                                    </div>
                                    <div class="col-8">
                                        <button type="submit" name="simpan_transaksi" class="btn btn-primary-custom w-100 py-2 fw-bold shadow-sm" style="border-radius: 12px;">
                                            Simpan <i class="bi bi-check-lg ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

    <!-- BOTTOM NAV MOBILE -->
    <nav class="mobile-bottom-nav d-md-none">
        <a href="beranda.php" class="nav-item-mobile"><i class="bi bi-house-door-fill"></i><span>Beranda</span></a>
        <a href="budgeting.php" class="nav-item-mobile"><i class="bi bi-bullseye"></i><span>Budgeting</span></a>
        <a href="catat_transaksi.php" class="nav-item-mobile active" style="transform: translateY(-10px);"><i class="bi bi-plus-circle-fill" style="font-size: 2.2rem; filter: drop-shadow(0 4px 6px rgba(26,86,219,0.3));"></i><span style="margin-top: -3px;">Catat</span></a>
        <a href="riwayat_transaksi.php" class="nav-item-mobile"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
        <a href="analisa.php" class="nav-item-mobile"><i class="bi bi-pie-chart-fill"></i><span>Analisa</span></a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="catat_transaksi.js"></script>
</body>
</html>