<?php
session_start();
require 'fungsi.php';

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Ambil data untuk analisa
$ringkasan = get_ringkasan_bulan_ini($user_id);
$stat_kategori = get_stat_kategori_bulan_ini($user_id);
$stat_6_bulan  = get_stat_6_bulan($user_id);

// Cari kategori terboros bulan ini
$kategori_terboros = "Belum ada data";
$nominal_terboros = 0;
if (!empty($stat_kategori)) {
    $kategori_terboros = $stat_kategori[0]['kategori'];
    $nominal_terboros  = $stat_kategori[0]['total'];
}

// Logika Smart Insight / Kesimpulan Otomatis
$teks_insight = "";
if ($ringkasan['pengeluaran'] == 0 && $ringkasan['pemasukan'] == 0) {
    $teks_insight = "Belum ada aktivitas transaksi bulan ini. Mulai catat pengeluaran dan pemasukanmu untuk mengaktifkan asisten analisa finansial!";
} elseif ($ringkasan['pengeluaran'] > $ringkasan['pemasukan'] && $ringkasan['pemasukan'] > 0) {
    $teks_insight = "⚠️ <b>Perhatian: Arus Kas Defisit!</b> Pengeluaranmu bulan ini sudah melebihi pemasukan. Pengeluaran terbesar jatuh pada kategori <b>" . htmlspecialchars($kategori_terboros) . " (Rp " . number_format($nominal_terboros, 0, '', '.') . ")</b>. Segera rem pengeluaran non-esensialmu!";
} else {
    if (!empty($stat_kategori)) {
        $persen_terboros = ($ringkasan['pengeluaran'] > 0) ? round(($nominal_terboros / $ringkasan['pengeluaran']) * 100) : 0;
        $teks_insight = "🟢 <b>Arus Kas Sehat!</b> Pengeluaran terbesarmu bulan ini tersedot ke kategori <b>" . htmlspecialchars($kategori_terboros) . " (" . $persen_terboros . "% dari total pengeluaran)</b>. Pertahankan kebiasaan mencatatmu agar target barang impian di menu Budgeting cepat terealisasi!";
    } else {
        $teks_insight = "🟢 <b>Arus Kas Sehat!</b> Kamu memiliki pemasukan bulan ini dan belum ada pengeluaran yang tercatat. Selamat menabung!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisa Keuangan - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="analisa.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="riwayat_transaksi.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-calculator me-3 fs-5"></i> Riwayat Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="analisa.php" class="nav-link nav-link-custom active d-flex align-items-center">
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
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Analisa Keuangan</h4>
                <div class="d-flex align-items-center pe-3">
                    <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark-custom lh-1"><?= $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                    </div>
                </div>
            </header>

            <!-- STANDAR LEBAR 1100PX -->
            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                
                <!-- ROW 1: WIDGET RINGKASAN CEPAT (3 KARTU #b9dfff) -->
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
                            <div class="icon-summary bg-primary-custom text-white mb-2"><i class="bi bi-fire"></i></div>
                            <span class="text-secondary-custom small fw-semibold d-block">Kategori Terboros</span>
                            <h5 class="fw-bold text-dark-custom mb-0 text-truncate"><?= htmlspecialchars($kategori_terboros); ?></h5>
                            <?php if ($nominal_terboros > 0): ?>
                                <span class="text-secondary-custom" style="font-size: 0.75rem; font-weight: 700;">Rp <?= number_format($nominal_terboros, 0, '', '.'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: AREA GRAFIK VISUAL (2 KOLOM SEJAJAR) -->
                <div class="row g-4 mb-4">
                    
                    <!-- GRAFIK 1: KOMPOSISI PENGELUARAN (DOUGHNUT) -->
                    <div class="col-lg-6">
                        <div class="chart-card d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-dark-custom mb-0"><i class="bi bi-pie-chart-fill text-primary-custom me-2"></i>Komposisi Belanja</h5>
                                    <span class="badge bg-light text-secondary border fw-bold">Bulan Ini</span>
                                </div>
                                <p class="text-secondary-custom small mb-2">Persentase pengeluaran berdasarkan kategori</p>
                            </div>
                            
                            <div class="chart-container">
                                <?php if (empty($stat_kategori)): ?>
                                    <div class="text-center text-secondary small fw-semibold">Belum ada pengeluaran tercatat bulan ini</div>
                                <?php else: ?>
                                    <canvas id="donutChart"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- GRAFIK 2: TREN ARUS KAS (BAR CHART) -->
                    <div class="col-lg-6">
                        <div class="chart-card d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold text-dark-custom mb-0"><i class="bi bi-bar-chart-line-fill text-primary-custom me-2"></i>Tren Arus Kas</h5>
                                    <span class="badge bg-light text-secondary border fw-bold">6 Bulan Terakhir</span>
                                </div>
                                <p class="text-secondary-custom small mb-2">Perbandingan Pemasukan vs Pengeluaran</p>
                            </div>
                            
                            <div class="chart-container">
                                <canvas id="barChart"></canvas>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ROW 3: KARTU REKOMENDASI & KESIMPULAN PINTAR -->
                <div class="card-insight mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <i class="bi bi-lightbulb-fill fs-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark-custom mb-0">Kesimpulan Analisa Keuanganmu</h5>
                    </div>
                    <p class="text-secondary-custom mb-0 lh-lg" style="font-size: 0.95rem;">
                        <?= $teks_insight; ?>
                    </p>
                </div>

            </div>
        </main>
    </div>

    <!-- BOTTOM NAV MOBILE -->
    <nav class="mobile-bottom-nav d-md-none">
        <a href="beranda.php" class="nav-item-mobile"><i class="bi bi-house-door-fill"></i><span>Beranda</span></a>
        <a href="budgeting.php" class="nav-item-mobile"><i class="bi bi-bullseye"></i><span>Budgeting</span></a>
        <a href="catat_transaksi.php" class="nav-item-mobile text-primary-custom" style="transform: translateY(-10px);"><i class="bi bi-plus-circle-fill" style="font-size: 2.2rem; filter: drop-shadow(0 4px 6px rgba(26,86,219,0.3));"></i><span style="margin-top: -3px;">Catat</span></a>
        <a href="riwayat_transaksi.php" class="nav-item-mobile"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
        <a href="analisa.php" class="nav-item-mobile active"><i class="bi bi-pie-chart-fill"></i><span>Analisa</span></a>
    </nav>

    <!-- OPER KODINGAN DATA DARI PHP KE JAVASCRIPT -->
    <script>
        const dataKategori = <?= json_encode($stat_kategori); ?>;
        const data6Bulan   = <?= json_encode($stat_6_bulan); ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="analisa.js"></script>
</body>
</html>