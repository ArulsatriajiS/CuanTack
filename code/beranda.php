<?php
session_start();
require 'fungsi.php';

// Keamanan: Cek apakah user sudah login
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

// Ambil ID dan Nama dari Session (Sudah diperbaiki tanpa huruf 's')
$user_id   = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$nama_user = isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Pengguna';

// ==========================================
// LOGIKA 1-CLICK BUY (BELI BARANG IMPIAN OTOMATIS)
// ==========================================
if (isset($_GET['beli_otomatis']) && $_GET['beli_otomatis'] == 'true') {
    $data_rencana = get_rencana($user_id);
    if ($data_rencana && !empty($data_rencana['harga_barang']) && $data_rencana['harga_barang'] > 0) {
        $transaksi_beli = [
            'jenis' => 'Pengeluaran',
            'tanggal' => date('Y-m-d'), // Otomatis tanggal hari ini
            'jumlah' => $data_rencana['harga_barang'],
            'kategori' => 'Impian: ' . $data_rencana['nama_barang']
        ];
        if (simpan_transaksi($transaksi_beli, $user_id) > 0) {
            echo "<script>
                    alert('🎉 Selamat! Pembelian " . htmlspecialchars(addslashes($data_rencana['nama_barang'])) . " seharga Rp " . number_format($data_rencana['harga_barang'], 0, '', '.') . " berhasil dicatat ke Riwayat Transaksi!'); 
                    window.location='beranda.php';
                  </script>";
            exit;
        }
    }
}

// AMBIL DATA DARI DATABASE
$rencana   = get_rencana($user_id);
$ringkasan = get_ringkasan_bulan_ini($user_id); // Mengambil Pemasukan, Pengeluaran & Sisa Uang
$riwayat   = get_riwayat($user_id);              // Mengambil seluruh riwayat transaksi

// Ambil 3 transaksi terbaru saja untuk ditampilkan di Beranda
$riwayat_terbaru = array_slice($riwayat, 0, 3);

// Array kalimat motivasi acak
$quotes = [
    "Yuk, catat pengeluaranmu hari ini agar dompet tetap aman!",
    "Sedikit demi sedikit, lama-lama menjadi bukit. Semangat menabung!",
    "Jangan biarkan gajimu numpang lewat. Lacak sekarang!",
    "Kesehatan finansial dimulai dari satu catatan kecil hari ini.",
    "Ingat target finansialmu! Jangan goyah dengan diskon palsu hari ini 😂",
    "Kelola uangmu dengan bijak hari ini, nikmati hasilnya di masa depan."
];
$random_quote = $quotes[array_rand($quotes)];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS untuk Dashboard -->
    <link rel="stylesheet" href="beranda.css">
</head>
<body class="bg-main">

    <!-- Wrapper Utama -->
    <div class="d-flex vh-100 overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="sidebar-custom d-flex flex-column p-3">
            <div class="d-flex align-items-center mb-5 mt-2 px-2">
                <img src="../images/Logo_1.1.png" alt="Logo CuanTrack" style="height: 35px;" class="me-2">
                <span class="fs-4 fw-bold text-dark-custom">Cuan Track</span>
            </div>

            <!-- Menu Navigasi -->
            <ul class="nav nav-pills flex-column mb-auto gap-2">
                <li class="nav-item">
                    <a href="beranda.php" class="nav-link nav-link-custom active d-flex align-items-center">
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
                        <i class="bi bi-calendar2-week me-3 fs-5"></i> Catat Transaksi
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
            
            <!-- Header Atas (Top Bar) -->
            <header class="d-flex justify-content-between align-items-center p-4 border-bottom bg-white">
                <!-- Judul Kiri -->
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Pengaturan Akun</h4>
                
                <!-- Dropdown Profil Kanan -->
                <div class="dropdown pe-3">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        
                        <!-- Logika Tampilkan Foto / Ikon -->
                        <?php if (!empty($_SESSION['foto_profil']) && file_exists('../images/profil/' . $_SESSION['foto_profil'])): ?>
                            <img src="../images/profil/<?= $_SESSION['foto_profil']; ?>" alt="Foto Profil" class="rounded-circle object-fit-cover me-3 shadow-sm" style="width: 45px; height: 45px; border: 2px solid #1a56db;">
                        <?php else: ?>
                            <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                        <?php endif; ?>

                        <div class="d-flex flex-column text-start">
                            <span class="fw-bold text-dark-custom lh-1"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pengguna'); ?></span>
                            <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                        </div>
                    </a>
                    
                    <!-- Isi Menu Melayang -->
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-3 mt-2" aria-labelledby="dropdownUser" style="width: 250px; background-color: #ffffff;">
                        <li class="px-2 py-1 mb-2 d-flex align-items-center">
                            <?php if (!empty($_SESSION['foto_profil']) && file_exists('../images/profil/' . $_SESSION['foto_profil'])): ?>
                                <img src="../images/profil/<?= $_SESSION['foto_profil']; ?>" alt="Foto Profil" class="rounded-circle object-fit-cover me-3" style="width: 40px; height: 40px;">
                            <?php else: ?>
                                <i class="bi bi-person-circle text-secondary fs-2 me-3"></i>
                            <?php endif; ?>
                            <div class="overflow-hidden">
                                <span class="fw-bold text-dark-custom d-block text-truncate"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pengguna'); ?></span>
                                <small class="text-secondary d-block text-truncate"><?= htmlspecialchars($_SESSION['email'] ?? 'Akun CuanTrack'); ?></small>
                            </div>
                        </li>
                        <li><span class="badge bg-success-subtle text-success border border-success-subtle w-100 py-1 mt-1" style="font-size: 0.7rem;">🟢 Pengguna Aktif</span></li>
                        <li><hr class="dropdown-divider opacity-10 my-2"></li>
                        
                        <li>
                            <a class="dropdown-item rounded-3 py-2 fw-semibold text-dark-custom d-flex align-items-center" href="pengaturan.php">
                                <i class="bi bi-gear-fill text-primary-custom me-2 fs-6"></i> Pengaturan Akun
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 fw-semibold text-dark-custom d-flex align-items-center" href="analisa.php">
                                <i class="bi bi-pie-chart-fill text-primary-custom me-2 fs-6"></i> Analisa Keuangan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider opacity-10 my-2"></li>
                        
                        <li>
                            <a class="dropdown-item rounded-3 py-2 fw-bold text-danger d-flex align-items-center" href="logout.php" onclick="return confirm('Yakin ingin keluar dari aplikasi CuanTrack?');">
                                <i class="bi bi-box-arrow-right me-2 fs-6"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- Area Body Konten (Standar Lebar 1100px) -->
            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                
                <!-- Sapaan & Kalimat Motivasi -->
                <div class="mb-4 text-center text-md-start">
                    <h3 class="fw-bold text-dark-custom mb-2">
                        Hallo, selamat datang <?= htmlspecialchars($nama_user); ?> 👋
                    </h3>
                    <p class="text-secondary-custom fs-6 mb-0">
                        <?= $random_quote; ?>
                    </p>
                </div>

                <!-- CARD SALDO UTAMA (SISA SALDO / NET CASHFLOW) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 card-soft-blue">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-secondary-custom fs-5 mb-2">Sisa Saldo Bulan Ini</p>
                            <h2 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($ringkasan['selisih'], 0, '', '.'); ?></h2>
                        </div>
                        <div class="wallet-icon-wrapper">
                            <i class="bi bi-cash-stack text-primary-custom" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                </div>

                <!-- ROW 2 KOLOM (BERDAMPINGAN: PEMASUKAN vs PENGELUARAN) -->
                <div class="row g-4 mb-4">
                    <!-- Kolom Kiri: PEMASUKAN -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 card-soft-blue">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-green-light text-success me-3">
                                    <i class="bi bi-arrow-down-left fs-4"></i>
                                </div>
                                <span class="text-secondary-custom fw-semibold">Pemasukan</span>
                            </div>
                            <h4 class="fw-bold text-success mb-0 ms-2">+ Rp <?= number_format($ringkasan['pemasukan'], 0, '', '.'); ?></h4>
                        </div>
                    </div>

                    <!-- Kolom Kanan: PENGELUARAN -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 card-soft-blue">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-blue-light text-danger me-3" style="background-color: #fee2e2;">
                                    <i class="bi bi-arrow-up-right fs-4"></i>
                                </div>
                                <span class="text-secondary-custom fw-semibold">Pengeluaran</span>
                            </div>
                            <h4 class="fw-bold text-danger mb-0 ms-2">- Rp <?= number_format($ringkasan['pengeluaran'], 0, '', '.'); ?></h4>
                        </div>
                    </div>
                </div>

                <!-- CARD TARGET BELI (Terhubung dengan Catat Transaksi -> Kategori: Belanja) -->
                <div class="card border-0 rounded-4 p-4 mb-4 card-soft-blue shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bullseye fs-4 text-primary-custom me-3"></i>
                            <h5 class="fw-bold text-dark-custom mb-0">
                                <?= !empty($rencana['nama_barang']) ? 'Target Beli: ' . ucwords(htmlspecialchars($rencana['nama_barang'])) : 'Target Bulan Ini'; ?>
                            </h5>
                        </div>
                        <?php if ($rencana && !empty($rencana['nama_barang'])): ?>
                            <a href="budgeting.php" class="text-decoration-none text-primary-custom small fw-bold">Edit Target <i class="bi bi-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($rencana && !empty($rencana['harga_barang']) && $rencana['harga_barang'] > 0): ?>
                        <?php 
                            $target_harga = $rencana['harga_barang'];
                            
                            // LOGIKA PINTAR: Mengecek pembelian menggunakan Trik Kategori Unik
                            $terkumpul = 0;
                            $bulan_ini = date('m');
                            $tahun_ini = date('Y');
                            $kategori_impian = 'Impian: ' . $rencana['nama_barang']; // <-- Kunci Rahasia
                            
                            if (!empty($riwayat)) {
                                foreach ($riwayat as $t) {
                                    $t_bulan = date('m', strtotime($t['tanggal']));
                                    $t_tahun = date('Y', strtotime($t['tanggal']));
                                    if ($t['jenis'] == 'Pengeluaran' && $t['kategori'] == $kategori_impian && $t_bulan == $bulan_ini && $t_tahun == $tahun_ini) {
                                        $terkumpul += $t['jumlah'];
                                    }
                                }
                            }
                            
                            $persentase = min(100, ($terkumpul / $target_harga) * 100);
                        ?>
                        
                        <?php if ($persentase >= 100): ?>
                            <!-- STATUS: SUDAH DIBELI (100% FULL) -->
                            <div class="alert alert-success border-0 rounded-3 p-3 mb-1 d-flex align-items-center shadow-sm" style="background-color: #d1fae5; color: #065f46;">
                                <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">🎉 Selamat! Barang Impian Berhasil Dibeli!</h6>
                                    <small>Kamu telah mencatat pengeluaran belanja sebesar Rp <?= number_format($terkumpul, 0, '', '.'); ?> bulan ini. Targetmu tercapai!</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- STATUS: BELUM DIBELI / PROGRES -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary-custom" style="font-size: 0.75rem; font-weight: 600;">Progres pembelian (terdeteksi dari pengeluaran kategori 'Belanja')</span>
                                <span class="text-primary-custom fw-bold" style="font-size: 0.75rem;"><?= round($persentase); ?>%</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px; background-color: rgba(255,255,255,0.6); border-radius: 10px;">
                                <div class="progress-bar bg-primary-custom" role="progressbar" style="width: <?= $persentase; ?>%; border-radius: 10px;" aria-valuenow="<?= $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-dark-custom fw-bold small mb-3">
                                <span class="text-secondary-custom">Terpakai Belanja: Rp <?= number_format($terkumpul, 0, '', '.'); ?></span>
                                <span>Target Harga: Rp <?= number_format($target_harga, 0, '', '.'); ?></span>
                            </div>

                            <!-- TOMBOL 1-CLICK BUY -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                                <span class="small text-secondary-custom fw-semibold">Sudah membeli barang ini?</span>
                                <a href="beranda.php?beli_otomatis=true" onclick="return confirm('Yakin ingin mencatat pembelian <?= htmlspecialchars(addslashes($rencana['nama_barang'])); ?> seharga Rp <?= number_format($target_harga, 0, '', '.'); ?> ke pengeluaran Belanja sekarang?');" class="btn btn-sm btn-primary-custom px-4 py-2 fw-bold rounded-pill shadow-sm d-flex align-items-center">
                                    <i class="bi bi-cart-check-fill me-2 fs-6"></i> Beli Sekarang
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-2">
                            <p class="text-muted fw-semibold small mb-2">Kamu belum mengatur barang impian yang ingin dibeli bulan ini.</p>
                            <a href="budgeting.php" class="btn btn-sm btn-primary-custom px-3 py-1 rounded-pill fw-bold">Atur Target Beli Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CARD RIWAYAT TRANSAKSI (Otomatis Memunculkan 3 Transaksi Terbaru) -->
                <div class="card border-0 rounded-4 p-4 mb-5 card-soft-blue shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock-history fs-4 text-dark-custom me-3"></i>
                            <h5 class="fw-bold text-dark-custom mb-0">Riwayat transaksi terbaru</h5>
                        </div>
                        <?php if (!empty($riwayat_terbaru)): ?>
                            <a href="riwayat_transaksi.php" class="text-decoration-none text-primary-custom small fw-bold">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($riwayat_terbaru)): ?>
                        <p class="text-muted fw-semibold ms-5 mb-0">Belum ada transaksi yang dicatat</p>
                    <?php else: ?>
                        <div class="mt-2">
                            <?php foreach ($riwayat_terbaru as $t): ?>
                                <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded-3 bg-white shadow-sm border" style="border-color: rgba(0,0,0,0.05) !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light text-primary-custom rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="bi bi-receipt fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark-custom"><?= htmlspecialchars($t['kategori']); ?></h6>
                                            <small class="text-secondary" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($t['tanggal'])); ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold <?= $t['jenis'] == 'Pemasukan' ? 'text-success' : 'text-danger'; ?>">
                                        <?= $t['jenis'] == 'Pemasukan' ? '+' : '-'; ?> Rp <?= number_format($t['jumlah'], 0, '', '.'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <!-- Bottom Nav Mobile -->
    <nav class="mobile-bottom-nav d-md-none">
        <a href="beranda.php" class="nav-item-mobile active">
            <i class="bi bi-house-door-fill"></i>
            <span>Beranda</span>
        </a>
        <a href="budgeting.php" class="nav-item-mobile">
            <i class="bi bi-bullseye"></i>
            <span>Budgeting</span>
        </a>
        <a href="catat_transaksi.php" class="nav-item-mobile text-primary-custom" style="transform: translateY(-10px);">
            <i class="bi bi-plus-circle-fill" style="font-size: 2.2rem; filter: drop-shadow(0 4px 6px rgba(26,86,219,0.3));"></i>
            <span style="margin-top: -3px;">Catat</span>
        </a>
        <a href="riwayat_transaksi.php" class="nav-item-mobile">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat</span>
        </a>
        <a href="analisa.php" class="nav-item-mobile">
            <i class="bi bi-pie-chart-fill"></i>
            <span>Analisa</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>