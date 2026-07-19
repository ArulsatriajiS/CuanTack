<?php
// Panggil file fungsi
require 'fungsi.php';

// Jika tombol daftar ditekan
if (isset($_POST['btn_daftar'])) {
    
    // Kirim semua data form ($_POST) ke fungsi registrasi()
    if (registrasi($_POST) > 0) {
        echo "<script>
                alert('Pendaftaran berhasil! Silakan Login.');
                window.location='login.php';
              </script>";
    } else {
        echo mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- File CSS khusus untuk Daftar -->
    <link rel="stylesheet" href="daftar.css">
</head>
<body class="register-bg">

    <!-- Container Utama -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        
        <!-- Card Daftar -->
        <div class="card register-card border-0 shadow-lg p-4 p-md-5">
            
            <!-- Ikon Panah Kembali -->
           <a href="index.html" class="back-btn text-dark-custom">
                <i class="bi bi-arrow-left" style="font-size: 1.6rem;"></i>
            </a>
            
            <!-- Header: Logo, Judul & Sub-judul -->
            <div class="text-center mb-4 mt-2">
                <!-- Logo & Teks Berdampingan -->
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <img src="../images/Logo_1.1.png" alt="Logo" style="height: 35px;" class="me-2">
                    <span class="fs-4 fw-bold text-dark-custom">Cuan Track</span>
                </div>
                
                <h3 class="fw-bold text-dark-custom mb-2">Daftar</h3>
                <p class="text-secondary-custom small mb-0 px-2 px-md-3">Daftarkan diri Anda untuk mengakses fitur pencatatan dan analisis keuangan</p>
            </div>

            <!-- Form Input -->
            <form action="" method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label fw-bold text-dark-custom small mb-1">Nama lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control custom-input" placeholder="Nama lengkapmu" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="form-label fw-bold text-dark-custom small mb-1">Email</label>
                    <input type="email" name="email" class="form-control custom-input" placeholder="nama@gmail.com" required>
                </div>
                
                <div class="mb-4 text-start">
                    <label class="form-label fw-bold text-dark-custom small mb-1">Kata sandi</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="passwordInput" class="form-control custom-input pe-5" placeholder="Masukkan kata sandi" required>
                        
                        <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3" id="togglePasswordIcon" style="cursor: pointer; color: #a1a1aa; font-size: 1.1rem;"></i>
                    </div>
                </div>
                
                <button type="submit" name="btn_daftar" class="btn btn-primary-custom w-100 fw-bold py-2 mb-3">Daftar</button>
            </form>

            <!-- Garis Pemisah (Divider) -->
            <div class="d-flex align-items-center mb-3">
                <hr class="flex-grow-1 divider-line">
                <span class="px-3 text-muted fw-semibold" style="font-size: 0.9rem;">atau</span>
                <hr class="flex-grow-1 divider-line">
            </div>

            <!-- Tombol Masuk dengan Google -->
            <button type="button" class="btn btn-google w-100 d-flex align-items-center justify-content-center fw-semibold py-2 mb-4">
                <img src="../images/google.png" alt="Google" style="width: 20px;" class="me-2">
                Masuk dengan Google
            </button>

            <div class="text-center">
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Sudah punya akun? <a href="login.php" class="text-decoration-none text-dark-custom fw-bold">Masuk sekarang</a>
                </p>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- File JS khusus untuk Login & Daftar -->
    <script src="aut.js"></script>
</body>
</html>