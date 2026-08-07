<?php
require 'fungsi.php';
global $koneksi;

// Cek apakah ada token di URL
if (!isset($_GET['token'])) {
    die("Akses ditolak! Token tidak valid.");
}

$token = mysqli_real_escape_string($koneksi, $_GET['token']);

// Cek apakah token ada di database dan belum kedaluwarsa
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE reset_token = '$token' AND reset_expired > NOW()");

if (mysqli_num_rows($query) === 0) {
    die("Link reset kata sandi tidak valid atau sudah kedaluwarsa. Silakan minta link baru.");
}

$user = mysqli_fetch_assoc($query);

// Jika form ganti sandi disubmit
if (isset($_POST['btn_simpan'])) {
    $sandi_baru = $_POST['sandi_baru'];
    
    if (strlen($sandi_baru) < 6) {
        echo "<script>alert('Kata sandi minimal 6 karakter!');</script>";
    } else {
        // Enkripsi kata sandi baru
        $password_hash = password_hash($sandi_baru, PASSWORD_DEFAULT);
        
        // Update password dan kosongkan token agar link tidak bisa dipakai lagi
        mysqli_query($koneksi, "UPDATE users SET password = '$password_hash', reset_token = NULL, reset_expired = NULL WHERE id = " . $user['id']);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Kata sandi berhasil diperbarui. Silakan login.',
                    icon: 'success'
                }).then((result) => {
                    window.location.href = 'login.php';
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Sandi Baru - CuanTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
</head>
<body class="login-bg">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card login-card border-0 shadow-lg p-4 p-md-5">
            <h4 class="fw-bold text-center mb-3">Buat Kata Sandi Baru</h4>
            <p class="text-muted text-center small mb-4">Silakan masukkan kata sandi baru untuk akun <strong><?= htmlspecialchars($user['email']) ?></strong>.</p>
            
            <form action="" method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold small">Kata Sandi Baru</label>
                    <input type="password" name="sandi_baru" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <button type="submit" name="btn_simpan" class="btn btn-success w-100 fw-bold py-2">Simpan Kata Sandi</button>
            </form>
        </div>
    </div>
</body>
</html>