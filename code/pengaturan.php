<?php
session_start();
require 'fungsi.php';

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$user_id   = $_SESSION["user_id"];
$nama_user = $_SESSION["nama_lengkap"] ?? 'Pengguna';

// Ambil data email pengguna saat ini
global $koneksi;
$stmt = mysqli_prepare($koneksi, "SELECT email FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email_user);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Proses saat tombol Ganti Sandi ditekan
$pesan_error  = "";
$pesan_sukses = "";

if (isset($_POST['btn_ganti_password'])) {
    $hasil = ganti_password($_POST, $user_id);
    if ($hasil === "SUKSES") {
        $pesan_sukses = "🎉 Kata sandi berhasil diperbarui! Gunakan kata sandi baru saat login berikutnya.";
    } else {
        $pesan_error = "⚠️ " . $hasil;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="pengaturan.css">
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
                    <a href="analisa.php" class="nav-link nav-link-custom d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill me-3 fs-5"></i> Analisa
                    </a>
                </li>
            </ul>
            <hr class="border-secondary opacity-25 mx-2">
            <ul class="nav nav-pills flex-column gap-2 mb-3">
                <li class="nav-item">
                    <a href="pengaturan.php" class="nav-link nav-link-custom active d-flex align-items-center">
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
                <h4 class="fw-bold text-dark-custom mb-0 ps-2">Pengaturan Akun</h4>
                <div class="d-flex align-items-center pe-3">
                    <i class="bi bi-person-circle text-secondary fs-1 me-3"></i>
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark-custom lh-1"><?= htmlspecialchars($nama_user); ?></span>
                        <span class="text-secondary small mt-1 fw-semibold">Pengguna aktif</span>
                    </div>
                </div>
            </header>

            <div class="p-4 p-md-5 mx-auto" style="max-width: 1100px; width: 100%;">
                
                <!-- NOTIFIKASI HASIL GANTI SANDI -->
                <?php if (!empty($pesan_sukses)): ?>
                    <div class="alert alert-success border-0 rounded-4 p-4 shadow-sm mb-4 d-flex align-items-center" style="background-color: #d1fae5; color: #065f46;">
                        <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                        <div class="fw-bold"><?= $pesan_sukses; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($pesan_error)): ?>
                    <div class="alert alert-danger border-0 rounded-4 p-4 shadow-sm mb-4 d-flex align-items-center" style="background-color: #fee2e2; color: #991b1b;">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                        <div class="fw-bold"><?= $pesan_error; ?></div>
                    </div>
                <?php endif; ?>

                <!-- KARTU 1: PROFIL PENGGUNA -->
                <div class="card-settings-blue shadow-sm">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-person-badge-fill text-primary-custom fs-3 me-3"></i>
                        <div>
                            <h5 class="fw-bold text-dark-custom mb-0">Informasi Profil Akun</h5>
                            <small class="text-secondary-custom">Data akun yang terdaftar di dalam sistem CuanTrack</small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark-custom mb-1">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-custom bg-light" value="<?= htmlspecialchars($nama_user); ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark-custom mb-1">Alamat Email</label>
                            <input type="email" class="form-control form-control-custom bg-light" value="<?= htmlspecialchars($email_user); ?>" readonly disabled>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: GANTI KATA SANDI -->
                <div class="card-settings-blue shadow-sm mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-shield-lock-fill text-primary-custom fs-3 me-3"></i>
                        <div>
                            <h5 class="fw-bold text-dark-custom mb-0">Ganti Kata Sandi</h5>
                            <small class="text-secondary-custom">Pastikan menggunakan kata sandi yang kuat dan mudah kamu ingat</small>
                        </div>
                    </div>

                    <form action="" method="POST">
                        <div class="row g-4 mb-4">
                            <!-- Kata Sandi Lama -->
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Kata Sandi Saat Ini</label>
                                <div class="position-relative">
                                    <input type="password" name="sandi_lama" id="sandiLama" class="form-control form-control-custom pe-5" placeholder="Masukkan kata sandi lamamu" required>
                                    <i class="bi bi-eye-slash toggle-password-icon" onclick="togglePass('sandiLama', this)"></i>
                                </div>
                            </div>

                            <!-- Kata Sandi Baru -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Kata Sandi Baru</label>
                                <div class="position-relative">
                                    <input type="password" name="sandi_baru" id="sandiBaru" class="form-control form-control-custom pe-5" placeholder="Minimal 6 karakter" required>
                                    <i class="bi bi-eye-slash toggle-password-icon" onclick="togglePass('sandiBaru', this)"></i>
                                </div>
                            </div>

                            <!-- Konfirmasi Kata Sandi Baru -->
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark-custom mb-1">Konfirmasi Kata Sandi Baru</label>
                                <div class="position-relative">
                                    <input type="password" name="konfirmasi_sandi" id="sandiKonfirmasi" class="form-control form-control-custom pe-5" placeholder="Ketik ulang kata sandi baru" required>
                                    <i class="bi bi-eye-slash toggle-password-icon" onclick="togglePass('sandiKonfirmasi', this)"></i>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3 border-top" style="border-color: rgba(0,0,0,0.06) !important;">
                            <button type="reset" class="btn btn-light border px-4 py-2 fw-bold rounded-3">Batal</button>
                            <button type="submit" name="btn_ganti_password" class="btn btn-primary-custom px-5 py-2 fw-bold rounded-3 shadow-sm">
                                🛡️ Update Kata Sandi <i class="bi bi-check-lg ms-1"></i>
                            </button>
                        </div>
                    </form>
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
        <a href="pengaturan.php" class="nav-item-mobile active"><i class="bi bi-gear-fill"></i><span>Pengaturan</span></a>
    </nav>

    <!-- SCRIPT INTIP KATA SANDI -->
    <script>
        function togglePass(inputId, iconEl) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconEl.classList.remove('bi-eye-slash');
                iconEl.classList.add('bi-eye');
            } else {
                input.type = 'password';
                iconEl.classList.remove('bi-eye');
                iconEl.classList.add('bi-eye-slash');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>