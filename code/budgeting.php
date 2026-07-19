<?php
session_start();
require 'fungsi.php';

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// ==========================================
// LOGIKA SIMPAN CICILAN CUSTOM (NABUNG & DARURAT)
// ==========================================
if (isset($_POST['simpan_cicilan'])) {
    $nominal = !empty($_POST['nominal_cicilan']) ? (int) str_replace('.', '', $_POST['nominal_cicilan']) : 0;
    $kategori = $_POST['kategori_cicilan']; 
    $label    = ($kategori == 'Investasi') ? 'Tabungan' : 'Dana Darurat';

    if ($nominal > 0) {
        $transaksi = [
            'jenis' => 'Pengeluaran',
            'tanggal' => date('Y-m-d'),
            'jumlah' => $nominal,
            'kategori' => $kategori
        ];
        if (simpan_transaksi($transaksi, $user_id) > 0) {
            echo "<script>alert('🎉 Berhasil menyisihkan $label sebesar Rp " . number_format($nominal, 0, '', '.') . "!'); window.location='budgeting.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('⚠️ Nominal tidak boleh kosong atau 0!');</script>";
    }
}

// ==========================================
// LOGIKA GANTI TARGET BARANG BARU
// ==========================================
if (isset($_POST['simpan_barang_baru'])) {
    $nama_barang  = trim($_POST['nama_barang'] ?? '');
    $harga_barang = !empty($_POST['harga_barang']) ? (int) str_replace('.', '', $_POST['harga_barang']) : 0;
    
    global $koneksi;
    $stmt = mysqli_prepare($koneksi, "UPDATE rencana_keuangan SET nama_barang = ?, harga_barang = ? WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $nama_barang, $harga_barang, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    echo "<script>alert('✨ Target barang impian baru ($nama_barang) berhasil diatur! Selamat menabung kembali.'); window.location='budgeting.php';</script>";
    exit;
}

// ==========================================
// LOGIKA 1-KLIK BUY (TARGET BELI BARANG) - TRIK KATEGORI UNIK
// ==========================================
if (isset($_GET['aksi']) && $_GET['aksi'] == 'beli') {
    $data_rencana = get_rencana($user_id);
    if ($data_rencana && !empty($data_rencana['harga_barang']) && $data_rencana['harga_barang'] > 0) {
        $transaksi = [
            'jenis' => 'Pengeluaran',
            'tanggal' => date('Y-m-d'),
            'jumlah' => $data_rencana['harga_barang'],
            'kategori' => 'Impian: ' . $data_rencana['nama_barang'] // <-- Trik Kategori Unik
        ];
        if (simpan_transaksi($transaksi, $user_id) > 0) {
            echo "<script>alert('🎉 Selamat! Pembelian " . htmlspecialchars(addslashes($data_rencana['nama_barang'])) . " berhasil dicatat!'); window.location='budgeting.php';</script>";
            exit;
        }
    }
}

// Jika tombol "Simpan" pada formulir setup ditekan
if (isset($_POST['simpan_rencana'])) {
    simpan_rencana($_POST, $user_id);
    header("Location: budgeting.php");
    exit;
}

// Ambil data dari database
$rencana = get_rencana($user_id);
$riwayat = get_riwayat($user_id);

// Hitung akumulasi dana terkumpul
$terkumpul_beli    = 0;
$terkumpul_nabung  = 0;
$terkumpul_darurat = 0;

// Nama kategori unik untuk mendeteksi apakah barang INI sudah dibeli
$kategori_impian = 'Impian: ' . ($rencana['nama_barang'] ?? '');

if (!empty($riwayat)) {
    foreach ($riwayat as $t) {
        if ($t['jenis'] == 'Pengeluaran') {
            if ($t['kategori'] == $kategori_impian) { // <-- Logika Filter Reset
                $terkumpul_beli += $t['jumlah'];
            } elseif ($t['kategori'] == 'Investasi') {
                $terkumpul_nabung += $t['jumlah'];
            } elseif ($t['kategori'] == 'Lainnya') {
                $terkumpul_darurat += $t['jumlah'];
            }
        }
    }
}

$is_edit = isset($_GET['edit']) || !$rencana;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgeting - CuanTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="budgeting.css">
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
                <li class="nav-item"><a href="beranda.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-house-door-fill me-3 fs-5"></i> Beranda</a></li>
                <li class="nav-item"><a href="budgeting.php" class="nav-link nav-link-custom active d-flex align-items-center"><i class="bi bi-bullseye me-3 fs-5"></i> Budgeting</a></li>
                <li class="nav-item"><a href="catat_transaksi.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-calendar2-plus-fill me-3 fs-5"></i> Catat Transaksi</a></li>
                <li class="nav-item"><a href="riwayat_transaksi.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-calculator me-3 fs-5"></i> Riwayat Transaksi</a></li>
                <li class="nav-item"><a href="analisa.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-pie-chart-fill me-3 fs-5"></i> Analisa</a></li>
            </ul>
            <hr class="border-secondary opacity-25 mx-2">
            <ul class="nav nav-pills flex-column gap-2 mb-3">
                <li class="nav-item"><a href="pengaturan.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-gear-fill me-3 fs-5"></i> Pengaturan</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link nav-link-custom d-flex align-items-center"><i class="bi bi-box-arrow-right me-3 fs-5"></i> Keluar</a></li>
            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-grow-1 d-flex flex-column overflow-y-auto">
            <header class="d-flex justify-content-between align-items-center p-4 border-bottom bg-white">
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Budgeting Keuangan</h4>
                <?php if (!$is_edit): ?>
                <div class="pe-3">
                    <a href="budgeting.php?edit=true" class="btn btn-primary-custom px-4 py-2 fw-bold rounded-3 text-decoration-none">
                        <i class="bi bi-pencil-square me-1"></i> Edit Rencana
                    </a>
                </div>
                <?php else: ?>
                <div class="d-flex align-items-center pe-3">
                    <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark-custom lh-1"><?= $_SESSION['nama_lengkap']; ?></span>
                        <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                    </div>
                </div>
                <?php endif; ?>
            </header>

            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                
                <?php if ($is_edit): ?>
                <!-- FORMULIR SETUP RENCANA -->
                <div class="mb-4">
                    <h3 class="fw-bold text-dark-custom mb-1">Atur Rencana & Target Keuanganmu</h3>
                    <p class="text-secondary-custom small">Isi data di bawah ini untuk mengaktifkan analisa otomatis metode 50/30/20.</p>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="bulan_darurat" id="inputBulanDarurat" value="<?= isset($rencana['bulan_darurat']) ? $rencana['bulan_darurat'] : 3 ?>">
                    <div class="form-section-card shadow-sm mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-bar-chart-fill text-primary-custom fs-4 me-2"></i>
                            <h5 class="fw-bold text-dark-custom mb-0">1. Estimasi Arus Kas Bulanan (Opsional)</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Pendapatan Bulanan (Rp)</label>
                                <input type="text" name="pendapatan" id="inputPendapatan" class="form-control form-control-custom input-rupiah" placeholder="Misal: 5.000.000" value="<?= isset($rencana['pendapatan']) && $rencana['pendapatan'] > 0 ? number_format($rencana['pendapatan'], 0, '', '.') : '' ?>" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Pengeluaran Bulanan (Rp)</label>
                                <input type="text" name="pengeluaran" id="inputPengeluaran" class="form-control form-control-custom input-rupiah" placeholder="Misal: 2.500.000" value="<?= isset($rencana['pengeluaran']) && $rencana['pengeluaran'] > 0 ? number_format($rencana['pengeluaran'], 0, '', '.') : '' ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-section-card shadow-sm mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-piggy-bank-fill text-primary-custom fs-4 me-2"></i>
                            <h5 class="fw-bold text-dark-custom mb-0">2. Target Nabung & Barang Impian</h5>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Total Target Nabung (Rp)</label>
                                <input type="text" name="target_nabung" class="form-control form-control-custom input-rupiah" placeholder="Misal: 12.000.000" value="<?= isset($rencana['target_nabung']) && $rencana['target_nabung'] > 0 ? number_format($rencana['target_nabung'], 0, '', '.') : '' ?>" required autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Lama Nabung (Bulan)</label>
                                <input type="number" name="lama_nabung" class="form-control form-control-custom" placeholder="12" value="<?= isset($rencana['lama_nabung']) ? $rencana['lama_nabung'] : 12 ?>" required>
                            </div>
                        </div>
                        <hr class="my-4 opacity-10">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Nama Barang Impian</label>
                                <input type="text" name="nama_barang" class="form-control form-control-custom" placeholder="Misal: Tas, Sepatu, Laptop..." value="<?= isset($rencana['nama_barang']) ? $rencana['nama_barang'] : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Perkiraan Harga (Rp)</label>
                                <input type="text" name="harga_barang" class="form-control form-control-custom input-rupiah" placeholder="0" value="<?= isset($rencana['harga_barang']) && $rencana['harga_barang'] > 0 ? number_format($rencana['harga_barang'], 0, '', '.') : '' ?>" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-section-card shadow-sm text-center mb-4">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <i class="bi bi-shield-fill-check text-primary-custom fs-4 me-2"></i>
                            <h5 class="fw-bold text-dark-custom mb-0">3. Ketahanan Dana Darurat</h5>
                        </div>
                        <p class="text-secondary-custom small mb-3">Dana darurat dihitung otomatis berdasarkan pengeluaran bulananmu.</p>
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <button type="button" class="btn-option-pill <?= (isset($rencana['bulan_darurat']) && $rencana['bulan_darurat'] == 3) || !$rencana ? 'active' : '' ?>" onclick="pilihBulan(this, 3)">3 Bulan</button>
                            <button type="button" class="btn-option-pill <?= isset($rencana['bulan_darurat']) && $rencana['bulan_darurat'] == 6 ? 'active' : '' ?>" onclick="pilihBulan(this, 6)">6 Bulan</button>
                            <button type="button" class="btn-option-pill <?= isset($rencana['bulan_darurat']) && $rencana['bulan_darurat'] == 12 ? 'active' : '' ?>" onclick="pilihBulan(this, 12)">12 Bulan</button>
                        </div>
                        <div class="d-inline-block bg-light px-4 py-2 rounded-3 small fw-bold text-secondary-custom" id="teksDanaDarurat">Dana darurat: Rp 0</div>
                    </div>
                    <div class="d-flex justify-content-end gap-3 mb-5">
                        <?php if ($rencana): ?>
                            <a href="budgeting.php" class="btn btn-light px-4 py-3 fw-bold rounded-3 border">Batal</a>
                        <?php endif; ?>
                        <button type="submit" name="simpan_rencana" class="btn btn-primary-custom px-5 py-3 fw-bold rounded-3 fs-6 shadow-sm">Simpan & Lihat Analisa <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </form>

                <?php else: ?>
                <!-- DASHBOARD TARGET & ANALISA -->
                <?php 
                    $total_darurat = $rencana['pengeluaran'] * $rencana['bulan_darurat'];
                    $total_semua   = $rencana['target_nabung'] + $total_darurat + $rencana['harga_barang'];
                ?>
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm mb-4 border" style="border-color: rgba(26, 86, 219, 0.15) !important;">
                    <div class="d-flex align-items-center">
                        <div class="bg-light text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;"><i class="bi bi-wallet-fill fs-5"></i></div>
                        <div>
                            <span class="text-secondary-custom small d-block fw-semibold">Total Kebutuhan Dana Rencanamu</span>
                            <h5 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($total_semua, 0, '', '.'); ?></h5>
                        </div>
                    </div>
                    <span class="badge bg-primary-custom px-3 py-2 rounded-pill fw-semibold">Rencana Aktif 🟢</span>
                </div>

                <!-- KARTU 1: TARGET NABUNG -->
                <?php 
                    $per_bulan = $rencana['target_nabung'] / ($rencana['lama_nabung'] > 0 ? $rencana['lama_nabung'] : 1);
                    $persen_nabung = ($rencana['target_nabung'] > 0) ? min(100, ($terkumpul_nabung / $rencana['target_nabung']) * 100) : 0;
                ?>
                <div class="budgeting-card-blue shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-piggy-bank-fill text-primary-custom fs-3 me-3"></i>
                        <h5 class="fw-bold text-dark-custom mb-0">Target Nabung</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <div>
                            <h5 class="fw-bold text-dark-custom mb-1">Nabung <?= $rencana['lama_nabung']; ?> Bulan</h5>
                            <span class="text-secondary-custom small fw-semibold">Target nabung: Rp <?= number_format($rencana['target_nabung'], 0, '', '.'); ?></span>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($per_bulan, 0, '', '.'); ?></h6>
                            <span class="text-secondary-custom small fw-semibold">/ Bulan</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                        <span class="text-secondary-custom" style="font-size: 0.75rem; font-weight: 600;">Progres menabung (dari kategori 'Investasi')</span>
                        <span class="text-primary-custom fw-bold" style="font-size: 0.75rem;"><?= round($persen_nabung); ?>%</span>
                    </div>
                    <div class="progress progress-dark mb-3" style="height: 10px;">
                        <div class="progress-bar bg-primary-custom" role="progressbar" style="width: <?= $persen_nabung; ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                        <span class="small text-secondary-custom fw-semibold">Terkumpul: Rp <?= number_format($terkumpul_nabung, 0, '', '.'); ?></span>
                        <button type="button" class="btn btn-sm btn-primary-custom px-4 py-2 fw-bold rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNabung">
                            💰 Nyicil Sekarang
                        </button>
                    </div>
                </div>

                <!-- KARTU 2: DANA DARURAT -->
                <?php $persen_darurat = ($total_darurat > 0) ? min(100, ($terkumpul_darurat / $total_darurat) * 100) : 0; ?>
                <div class="budgeting-card-blue shadow-sm">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-fill-check text-primary-custom fs-3 me-3"></i>
                        <h5 class="fw-bold text-dark-custom mb-0">Dana Darurat</h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <h5 class="fw-bold text-dark-custom mb-1">Dana darurat <?= $rencana['bulan_darurat']; ?> Bulan</h5>
                            <span class="text-secondary-custom small fw-semibold">Berdasarkan pengeluaran Rp <?= number_format($rencana['pengeluaran'], 0, '', '.'); ?>/bulan</span>
                        </div>
                        <div class="text-end">
                            <h6 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($total_darurat, 0, '', '.'); ?></h6>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                        <span class="text-secondary-custom" style="font-size: 0.75rem; font-weight: 600;">Progres terkumpul (dari kategori 'Lainnya')</span>
                        <span class="text-primary-custom fw-bold" style="font-size: 0.75rem;"><?= round($persen_darurat); ?>%</span>
                    </div>
                    <div class="progress progress-dark mb-3" style="height: 10px;">
                        <div class="progress-bar bg-primary-custom" role="progressbar" style="width: <?= $persen_darurat; ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                        <span class="small text-secondary-custom fw-semibold">Terkumpul: Rp <?= number_format($terkumpul_darurat, 0, '', '.'); ?></span>
                        <button type="button" class="btn btn-sm btn-primary-custom px-4 py-2 fw-bold rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDarurat">
                            🛡️ Sisihkan Sekarang
                        </button>
                    </div>
                </div>

                <!-- KARTU 3: TARGET BELI -->
                <div class="budgeting-card-blue shadow-sm mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-cart-fill text-primary-custom fs-3 me-3"></i>
                        <h5 class="fw-bold text-dark-custom mb-0">Target Beli</h5>
                    </div>
                    <?php if (!empty($rencana['nama_barang']) && $rencana['harga_barang'] > 0): ?>
                        <?php $persen_beli = min(100, ($terkumpul_beli / $rencana['harga_barang']) * 100); ?>
                        
                        <?php if ($persen_beli >= 100): ?>
                            <!-- STATUS: SUDAH DIBELI -->
                            <div class="alert alert-success border-0 rounded-3 p-4 mb-1 shadow-sm" style="background-color: #d1fae5; color: #065f46;">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-check-circle-fill fs-2 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">🎉 Selamat! Barang Impian Berhasil Dibeli!</h6>
                                        <small>Kamu telah mencatat pengeluaran untuk <b><?= htmlspecialchars($rencana['nama_barang']); ?></b>. Targetmu tercapai!</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end pt-2 border-top" style="border-color: rgba(6, 95, 70, 0.15) !important;">
                                    <button type="button" class="btn btn-sm btn-success px-4 py-2 fw-bold rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGantiBarang">
                                        🔄 Atur Target Barang Baru
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <div>
                                    <h5 class="fw-bold text-dark-custom mb-1"><?= ucwords(htmlspecialchars($rencana['nama_barang'])); ?></h5>
                                    <span class="text-secondary-custom small fw-semibold">Perkiraan barang impianmu</span>
                                </div>
                                <div class="text-end">
                                    <h6 class="fw-bold text-dark-custom mb-0">Rp <?= number_format($rencana['harga_barang'], 0, '', '.'); ?></h6>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
                                <span class="text-secondary-custom" style="font-size: 0.75rem; font-weight: 600;">Status Pembelian</span>
                                <span class="text-primary-custom fw-bold" style="font-size: 0.75rem;"><?= round($persen_beli); ?>%</span>
                            </div>
                            <div class="progress progress-dark mb-3" style="height: 10px;">
                                <div class="progress-bar bg-primary-custom" role="progressbar" style="width: <?= $persen_beli; ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                                <span class="small text-secondary-custom fw-semibold">Belum Dibeli</span>
                                <a href="budgeting.php?aksi=beli" onclick="return confirm('Yakin ingin mencatat pembelian <?= htmlspecialchars(addslashes($rencana['nama_barang'])); ?> seharga Rp <?= number_format($rencana['harga_barang'], 0, '', '.'); ?> sekarang?');" class="btn btn-sm btn-primary-custom px-4 py-2 fw-bold rounded-pill shadow-sm">
                                    🛒 Beli Sekarang
                                </a>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted fw-semibold small mb-2">Kamu belum mengatur barang impian yang ingin dibeli bulan ini.</p>
                            <a href="budgeting.php?edit=true" class="btn btn-sm btn-primary-custom px-3 py-2 rounded-pill fw-bold">Atur Target Beli Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ANALISA & SARAN KEUANGAN 50/30/20 -->
                <div class="mt-5 mb-5">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-lightbulb-fill text-warning fs-3 me-2"></i>
                        <h4 class="fw-bold text-dark-custom mb-0">Saran Pemakaian Uang (Metode 50/30/20)</h4>
                    </div>
                    <?php if ($rencana['pendapatan'] > 0): ?>
                        <?php 
                            $pend = $rencana['pendapatan'];
                            $kebutuhan = $pend * 0.50; $keinginan = $pend * 0.30; $tabungan  = $pend * 0.20;
                        ?>
                        <div class="row g-4 mb-4">
                            <div class="col-md-4"><div class="box-503020 shadow-sm"><div class="icon-box-503020 bg-primary-custom text-white">🥗</div><span class="badge bg-light text-primary-custom border mb-2 fw-bold">50% Kebutuhan Pokok</span><h4 class="fw-bold text-dark-custom mb-2">Rp <?= number_format($kebutuhan, 0, '', '.'); ?></h4></div></div>
                            <div class="col-md-4"><div class="box-503020 shadow-sm"><div class="icon-box-503020 text-white" style="background-color: #f59e0b;">🍿</div><span class="badge bg-light text-warning border mb-2 fw-bold" style="color: #d97706 !important;">30% Keinginan</span><h4 class="fw-bold text-dark-custom mb-2">Rp <?= number_format($keinginan, 0, '', '.'); ?></h4></div></div>
                            <div class="col-md-4"><div class="box-503020 shadow-sm"><div class="icon-box-503020 text-white" style="background-color: #10b981;">💰</div><span class="badge bg-light text-success border mb-2 fw-bold">20% Tabungan / Invest</span><h4 class="fw-bold text-dark-custom mb-2">Rp <?= number_format($tabungan, 0, '', '.'); ?></h4></div></div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- POPUP MODALS -->
    <div class="modal fade" id="modalNabung" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0 shadow-lg"><div class="modal-header border-bottom-0 pt-4 px-4"><h5 class="modal-title fw-bold text-dark-custom"><i class="bi bi-piggy-bank-fill text-primary-custom me-2"></i>Nyicil Tabungan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="" method="POST"><div class="modal-body px-4 py-3"><input type="hidden" name="kategori_cicilan" value="Investasi"><div class="mb-3"><label class="form-label small fw-bold text-dark-custom mb-1">Nominal (Rp)</label><input type="text" name="nominal_cicilan" class="form-control form-control-custom input-rupiah" placeholder="Misal: 150.000" required autocomplete="off" autofocus></div></div><div class="modal-footer border-top-0 pb-4 px-4"><button type="button" class="btn btn-light border px-4 py-2 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan_cicilan" class="btn btn-primary-custom px-4 py-2 fw-bold rounded-3">Simpan Tabungan</button></div></form></div></div></div>

    <div class="modal fade" id="modalDarurat" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0 shadow-lg"><div class="modal-header border-bottom-0 pt-4 px-4"><h5 class="modal-title fw-bold text-dark-custom"><i class="bi bi-shield-fill-check text-primary-custom me-2"></i>Sisihkan Dana Darurat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="" method="POST"><div class="modal-body px-4 py-3"><input type="hidden" name="kategori_cicilan" value="Lainnya"><div class="mb-3"><label class="form-label small fw-bold text-dark-custom mb-1">Nominal (Rp)</label><input type="text" name="nominal_cicilan" class="form-control form-control-custom input-rupiah" placeholder="Misal: 500.000" required autocomplete="off"></div></div><div class="modal-footer border-top-0 pb-4 px-4"><button type="button" class="btn btn-light border px-4 py-2 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan_cicilan" class="btn btn-primary-custom px-4 py-2 fw-bold rounded-3">Simpan Dana Darurat</button></div></form></div></div></div>

    <div class="modal fade" id="modalGantiBarang" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0 shadow-lg"><div class="modal-header border-bottom-0 pt-4 px-4"><h5 class="modal-title fw-bold text-dark-custom"><i class="bi bi-cart-plus-fill text-primary-custom me-2"></i>Buat Target Barang Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form action="" method="POST"><div class="modal-body px-4 py-3"><p class="text-secondary-custom small mb-3">Selamat atas tercapainya target sebelumnya! Sekarang, apa barang impian baru yang ingin kamu kejar?</p><div class="mb-3"><label class="form-label small fw-bold text-dark-custom mb-1">Nama Barang Impian Baru</label><input type="text" name="nama_barang" class="form-control form-control-custom" placeholder="Misal: Sepatu Running, Smartphone..." required autocomplete="off" autofocus></div><div class="mb-3"><label class="form-label small fw-bold text-dark-custom mb-1">Perkiraan Harga (Rp)</label><input type="text" name="harga_barang" class="form-control form-control-custom input-rupiah" placeholder="Misal: 1.500.000" required autocomplete="off"></div></div><div class="modal-footer border-top-0 pb-4 px-4"><button type="button" class="btn btn-light border px-4 py-2 fw-bold rounded-3" data-bs-dismiss="modal">Batal</button><button type="submit" name="simpan_barang_baru" class="btn btn-primary-custom px-4 py-2 fw-bold rounded-3">Simpan Target Baru</button></div></form></div></div></div>

    <nav class="mobile-bottom-nav d-md-none">
        <a href="beranda.php" class="nav-item-mobile"><i class="bi bi-house-door-fill"></i><span>Beranda</span></a>
        <a href="budgeting.php" class="nav-item-mobile active"><i class="bi bi-bullseye"></i><span>Budgeting</span></a>
        <a href="catat_transaksi.php" class="nav-item-mobile text-primary-custom" style="transform: translateY(-10px);"><i class="bi bi-plus-circle-fill" style="font-size: 2.2rem; filter: drop-shadow(0 4px 6px rgba(26,86,219,0.3));"></i><span style="margin-top: -3px;">Catat</span></a>
        <a href="riwayat_transaksi.php" class="nav-item-mobile"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
        <a href="pengaturan.php" class="nav-item-mobile"><i class="bi bi-gear-fill"></i><span>Pengaturan</span></a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="budgeting.js"></script>
</body>
</html>