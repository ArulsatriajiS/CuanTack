<?php
// Mulai session di baris paling atas
session_start();

// Panggil file fungsi
require 'fungsi.php';

// 1. Jika sudah login sebelumnya, langsung arahkan ke beranda
if (isset($_SESSION["login"])) {
    echo "<script>window.location.href = 'beranda.php';</script>";
    exit;
}

// ==========================================
// 2. KODE PENANGKAP LOGIN GOOGLE
// ==========================================
if (isset($_POST['credential'])) {
    $jwt = $_POST['credential'];
    $parts = explode('.', $jwt);
    
    // Memperbaiki format base64 Google
    $base64 = str_replace(['-', '_'], ['+', '/'], $parts[1]);
    $pad = strlen($base64) % 4;
    if ($pad) {
        $base64 .= str_repeat('=', 4 - $pad);
    }
    
    $payload = json_decode(base64_decode($base64), true);
    
    if ($payload && isset($payload['email'])) {
        $email_google = $payload['email'];
        $nama_google = $payload['name'];
        
        if (login_google($email_google, $nama_google)) {
            // Menggunakan JS Redirect (Anti Layar Putih!)
            echo "<script>window.location.href = 'beranda.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menyimpan data ke database!'); window.location.href='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Error: Data dari Google tidak terbaca!'); window.location.href='login.php';</script>";
        exit;
    }
}

// ==========================================
// 3. KODE PENANGKAP LOGIN MANUAL (DIKEMBALIKAN)
// ==========================================
if (isset($_POST['btn_login'])) {
    if (login($_POST)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Anda berhasil masuk',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Oke'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'beranda.php';
                    }
                });
            </script>";
        exit;
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    title: 'Login Gagal',
                    text: 'Email atau Kata Sandi salah!',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CuanTrack</title>
    <!-- Logo Website -->
    <link rel="icon" type="image/x-icon" href="../images/Logo_1.1.png">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- File CSS khusus untuk Login -->
    <link rel="stylesheet" href="login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="login-bg">

<!-- KODE PRELOADER (Tepat di bawah tag <body>) -->
<style>
    #preloader {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: #f8f9fa; /* Warna background loading */
        z-index: 9999;
        display: flex; flex-direction: column;
        justify-content: center; align-items: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }
    .spinner {
        width: 50px; height: 50px;
        border: 5px solid #e0e0e0;
        border-top: 5px solid #0d6efd; /* Warna biru CuanTrack */
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .loading-text {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333; font-weight: 600;
    }
</style>

<div id="preloader">
    <div class="spinner"></div>
    <div class="loading-text">Menyiapkan CuanTrack...</div>
</div>

<script>
    // Menghilangkan loading screen saat semua elemen halaman sudah siap
    window.addEventListener('load', function() {
        const preloader = document.getElementById('preloader');
        preloader.style.opacity = '0';
        setTimeout(() => {
            preloader.style.visibility = 'hidden';
            preloader.style.display = 'none';
        }, 500);
    });
</script>
<!-- AKHIR KODE PRELOADER -->

    <!-- Container Utama -->
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        
        <!-- Card Login -->
        <div class="card login-card border-0 shadow-lg p-4 p-md-5">
            
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
                
                <h3 class="fw-bold text-dark-custom mb-2">Masuk</h3>
                <p class="text-secondary-custom small mb-0 px-2 px-md-4">Silakan masuk untuk mengelola portofolio keuangan Anda.</p>
            </div>

            <!-- Form Input -->
           <form action="" method="POST">
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

                <div class="text-end mb-4">
                    <a href="lupa_sandi.php" class="text-decoration-none text-muted small fw-semibold">Lupa kata sandi?</a>
                </div>
                
                <button type="submit" name="btn_login" class="btn btn-primary-custom w-100 fw-bold py-2 mb-3">Masuk</button>
            </form>

            <!-- Garis Pemisah (Divider) -->
            <div class="d-flex align-items-center mb-3">
                <hr class="flex-grow-1 divider-line">
                <span class="px-3 text-muted fw-semibold" style="font-size: 0.9rem;">or</span>
                <hr class="flex-grow-1 divider-line">
            </div>

            <!-- Tombol Sign in Google -->
            <!-- Konfigurasi Google (Tidak terlihat di layar) -->
            <div id="g_id_onload"
                data-client_id="147298938133-k3sqkpmkc8d9d8musj174fl5uoqa8lab.apps.googleusercontent.com"
                data-context="signin"
                data-ux_mode="redirect"
                data-login_uri="https://cuantrack-app-bra4dtcydteyb2bh.indonesiacentral-01.azurewebsites.net/code/login.php"
                data-auto_prompt="false">
            </div>

            <!-- Tampilan Tombol Google-nya -->
            <!-- Tampilan Tombol Google-nya -->
            <div class="d-flex justify-content-center mb-4 w-100">
                <div class="g_id_signin"
                    data-type="standard"
                    data-shape="rectangular"
                    data-theme="outline"
                    data-text="continue_with"
                    data-size="large"
                    data-logo_alignment="center"
                    data-width="360">
                </div>
            </div>
            <!-- Teks Buat Akun -->
            <div class="text-center">
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Belum punya akun? <a href="daftar.php" class="text-decoration-none text-dark-custom fw-bold">Buat akun</a>
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