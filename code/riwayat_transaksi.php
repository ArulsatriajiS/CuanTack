<?php
session_start();
require 'fungsi.php';

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Logika Hapus Transaksi jika tombol sampah ditekan
if (isset($_GET['hapus'])) {
    if (hapus_transaksi($_GET['hapus'], $user_id) > 0) {
        echo "<script>alert('Transaksi berhasil dihapus!'); window.location='riwayat_transaksi.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus transaksi!'); window.location='riwayat_transaksi.php';</script>";
    }
}

// Tangkap Parameter Filter & Pencarian
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'Semua';
$keyword      = isset($_GET['cari']) ? $_GET['cari'] : '';

// Ambil data transaksi & ringkasan dari database
$transaksi = get_riwayat($user_id, $filter_jenis, $keyword);
$ringkasan = get_ringkasan_bulan_ini($user_id);

// Pemetaan Ikon berdasarkan Kategori
$ikon_kategori = [
    'Kebutuhan Pokok' => 'bi-cart3 text-primary bg-primary-subtle',
    'Transportasi'    => 'bi-scooter text-info bg-info-subtle',
    'Kesehatan'       => 'bi-capsule text-danger bg-danger-subtle',
    'Hiburan'         => 'bi-film text-warning bg-warning-subtle',
    'Belanja'         => 'bi-bag-heart text-purple bg-light',
    'Gaji'            => 'bi-wallet-fill text-success bg-success-subtle',
    'Bonus'           => 'bi-gift text-success bg-success-subtle',
    'Investasi'       => 'bi-graph-up-arrow text-primary bg-primary-subtle',
    'Freelance'       => 'bi-laptop text-info bg-info-subtle',
    'Hadiah'          => 'bi-envelope-paper-heart text-warning bg-warning-subtle',
    'Lainnya'         => 'bi-box-seam text-secondary bg-secondary-subtle'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS untuk Riwayat Transaksi -->
    <link rel="stylesheet" href="riwayat_transaksi.css">
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
                    <a href="catat_transaksi.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-calendar2-plus-fill me-3 fs-5"></i> Catat Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="riwayat_transaksi.php" class="nav-link nav-link-custom active d-flex align-items-center">
                        <i class="bi bi-calculator me-3 fs-5"></i> Riwayat Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analisa.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill me-3 fs-5"></i> Analisa
                    </a>
                </li>
            </ul>
            <hr class="border-secondary opacity-25 mx-2">
            <ul class="nav nav-pills flex-column gap-2 mb-3">
                <li class="nav-item"><a href="#" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-gear-fill me-3 fs-5"></i> Pengaturan</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-box-arrow-right me-3 fs-5"></i> Keluar</a></li>
            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-grow-1 d-flex flex-column overflow-y-auto">
            
            <header class="d-flex justify-content-between align-items-center p-4 border-bottom bg-white">
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Riwayat Transaksi</h4>
                <div class="d-flex align-items-center pe-3">
                    <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark-custom lh-1"><?= $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                    </div>
                </div>
            </header>

            <!-- STANDAR LEBAR 1100PX (KONSISTEN DENGAN BERANDA & BUDGETING) -->
            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                
                <!-- ROW 1: RINGKASAN ARUS KAS BULAN INI (3 KARTU BIRU PASTEL) -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card-summary">
                            <div class="icon-summary bg-success text-white mb-2"><i class="bi bi-arrow-down-left"></i></div>
                            <span class="text-secondary-custom small fw-semibold d-block">Pemasukan Bulan Ini</span>
                            <h4 class="fw-bold text-success mb-0">+ Rp <?= number_format($ringkasan['pemasukan'], 0, '', '.'); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-summary">
                            <div class="icon-summary bg-danger text-white mb-2"><i class="bi bi-arrow-up-right"></i></div>
                            <span class="text-secondary-custom small fw-semibold d-block">Pengeluaran Bulan Ini</span>
                            <h4 class="fw-bold text-danger mb-0">- Rp <?= number_format($ringkasan['pengeluaran'], 0, '', '.'); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-summary">
                            <div class="icon-summary bg-primary-custom text-white mb-2"><i class="bi bi-wallet2"></i></div>
                            <span class="text-secondary-custom small fw-semibold d-block">Sisa Arus Kas Bulan Ini</span>
                            <h4 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($ringkasan['selisih'], 0, '', '.'); ?></h4>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: BARIS FILTER & PENCARIAN (TANPA TOMBOL FILTER YANG TIDAK PERLU) -->
                <div class="filter-card mb-4">
                    <form action="" method="GET" class="row g-2 align-items-center">
                        
                        <!-- Kolom Cari (Diperlebar menjadi col-md-6) -->
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-secondary"></i></span>
                                <input type="text" name="cari" class="form-control form-control-custom border-start-0" placeholder="Cari kategori atau tanggal... (Tekan Enter)" value="<?= htmlspecialchars($keyword); ?>">
                            </div>
                        </div>

                        <!-- Dropdown Jenis (Diperlebar menjadi col-md-5 / 6 jika tanpa reset) -->
                        <div class="<?= ($filter_jenis != 'Semua' || !empty($keyword)) ? 'col-md-5' : 'col-md-6'; ?>">
                            <select name="jenis" class="form-select form-select-custom" id="selectJenis">
                                <option value="Semua" <?= $filter_jenis == 'Semua' ? 'selected' : ''; ?>>Semua Transaksi (Masuk & Keluar)</option>
                                <option value="Pemasukan" <?= $filter_jenis == 'Pemasukan' ? 'selected' : ''; ?>>🟢 Hanya Pemasukan</option>
                                <option value="Pengeluaran" <?= $filter_jenis == 'Pengeluaran' ? 'selected' : ''; ?>>🔴 Hanya Pengeluaran</option>
                            </select>
                        </div>

                        <!-- Tombol Reset (Hanya muncul jika filter sedang aktif) -->
                        <?php if ($filter_jenis != 'Semua' || !empty($keyword)): ?>
                        <div class="col-md-1 d-flex justify-content-end">
                            <a href="riwayat_transaksi.php" class="btn btn-light border py-2 px-3 w-100 d-flex align-items-center justify-content-center bg-white" style="border-radius: 10px;" title="Reset Semua Filter"><i class="bi bi-arrow-counterclockwise fs-5 text-dark"></i></a>
                        </div>
                        <?php endif; ?>

                    </form>
                </div>
                
                <!-- ROW 3: DAFTAR TRANSAKSI (DIKELOMPOKKAN PER TANGGAL) -->
                <div>
                    <?php if (empty($transaksi)): ?>
                        <div class="text-center py-5 empty-card-custom rounded-4 border p-4 shadow-sm">
                            <i class="bi bi-inbox text-secondary fs-1 mb-2 d-block"></i>
                            <h6 class="fw-bold text-dark-custom">Belum Ada Transaksi yang Dicatat</h6>
                            <p class="text-secondary-custom small mb-3">Yuk mulai catat pengeluaran dan pemasukanmu agar keuanganmu terlacak dengan rapi!</p>
                            <a href="catat_transaksi.php" class="btn btn-primary-custom px-4 py-2 rounded-pill fw-bold small"><i class="bi bi-plus-lg me-1"></i> Catat Transaksi Sekarang</a>
                        </div>
                    <?php else: ?>
                        
                        <?php 
                        $tanggal_sementara = '';
                        $hari_ini = date('Y-m-d');
                        $kemarin  = date('Y-m-d', strtotime('-1 day'));

                        foreach ($transaksi as $t): 
                            // Logika Cetak Header Tanggal jika berbeda dari baris sebelumnya
                            if ($t['tanggal'] != $tanggal_sementara):
                                $tanggal_sementara = $t['tanggal'];
                                
                                // Format Nama Hari & Tanggal
                                if ($tanggal_sementara == $hari_ini) {
                                    $label_tanggal = "🔥 HARI INI - " . date('d M Y', strtotime($tanggal_sementara));
                                } elseif ($tanggal_sementara == $kemarin) {
                                    $label_tanggal = "⏱️ KEMARIN - " . date('d M Y', strtotime($tanggal_sementara));
                                } else {
                                    $label_tanggal = "📅 " . date('d M Y', strtotime($tanggal_sementara));
                                }
                        ?>
                            <div class="date-header"><?= $label_tanggal; ?></div>
                        <?php endif; ?>

                            <!-- KARTU ITEM TRANSAKSI -->
                            <div class="transaction-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <?php 
                                        $class_ikon = isset($ikon_kategori[$t['kategori']]) ? $ikon_kategori[$t['kategori']] : 'bi-box-seam text-secondary bg-light'; 
                                    ?>
                                    <div class="icon-category me-3 <?= $class_ikon; ?>">
                                        <i class="bi <?= explode(' ', $class_ikon)[0]; ?>"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark-custom mb-1"><?= htmlspecialchars($t['kategori']); ?></h6>
                                        <span class="text-secondary-custom" style="font-size: 0.75rem;">
                                            <span class="badge <?= $t['jenis'] == 'Pemasukan' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> me-1" style="font-size: 0.65rem;"><?= $t['jenis']; ?></span>
                                                <?= date('d/m/Y', strtotime($t['tanggal'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-end">
                                        <?php if ($t['jenis'] == 'Pemasukan'): ?>
                                            <h6 class="fw-bold text-success mb-0">+ Rp <?= number_format($t['jumlah'], 0, '', '.'); ?></h6>
                                        <?php else: ?>
                                            <h6 class="fw-bold text-dark-custom mb-0">- Rp <?= number_format($t['jumlah'], 0, '', '.'); ?></h6>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex align-items-center gap-1">
                                        <!-- Tombol Edit -->
                                        <a href="catat_transaksi.php?edit=<?= $t['id']; ?>" class="btn btn-sm btn-delete p-1" title="Edit Transaksi">
                                            <i class="bi bi-pencil-square fs-5 text-primary-custom"></i>
                                        </a>
                                        <!-- Tombol Hapus -->
                                        <a href="riwayat_transaksi.php?hapus=<?= $t['id']; ?>" class="btn btn-sm btn-delete p-1" onclick="return confirm('Yakin ingin menghapus transaksi <?= htmlspecialchars($t['kategori']); ?> ini?');" title="Hapus Transaksi">
                                            <i class="bi bi-trash3 fs-5"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <!-- BOTTOM NAV MOBILE -->
    <nav class="mobile-bottom-nav d-md-none">
        <a href="beranda.php" class="nav-item-mobile"><i class="bi bi-house-door-fill"></i><span>Beranda</span></a>
        <a href="budgeting.php" class="nav-item-mobile"><i class="bi bi-bullseye"></i><span>Budgeting</span></a>
        <a href="catat_transaksi.php" class="nav-item-mobile text-primary-custom" style="transform: translateY(-10px);"><i class="bi bi-plus-circle-fill" style="font-size: 2.2rem; filter: drop-shadow(0 4px 6px rgba(26,86,219,0.3));"></i><span style="margin-top: -3px;">Catat</span></a>
        <a href="riwayat_transaksi.php" class="nav-item-mobile active"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
        <a href="analisa.php" class="nav-item-mobile"><i class="bi bi-pie-chart-fill"></i><span>Analisa</span></a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="riwayat_transaksi.js"></script>
</body>
</html>