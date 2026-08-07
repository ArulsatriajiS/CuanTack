<?php
require 'fungsi.php';

if (isset($_POST['btn_reset'])) {
    global $koneksi;
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    // Cek apakah email terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        
        // Buat token unik dan waktu kedaluwarsa (1 jam dari sekarang)
        $token = bin2hex(random_bytes(32)); // Token rahasia acak
        $expired = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        // Simpan token ke database
        mysqli_query($koneksi, "UPDATE users SET reset_token = '$token', reset_expired = '$expired' WHERE email = '$email'");
        
        // Buat Link Reset
        $link_reset = "https://cuantrack-app-bra4dtcydteyb2bh.indonesiacentral-01.azurewebsites.net/code/reset_sandi.php?token=" . $token;
        
        // Setup Pengiriman Email (Menggunakan fungsi mail bawaan PHP)
        // Catatan: Di server Azure, ini mungkin butuh konfigurasi SMTP/PHPMailer lebih lanjut jika gagal terkirim.
        $to = $email;
        $subject = "Permintaan Reset Kata Sandi - CuanTrack";
        $message = "Halo,\n\nKami menerima permintaan untuk mereset kata sandi akun CuanTrack Anda.\n";
        $message .= "Silakan klik link berikut untuk membuat kata sandi baru (berlaku selama 1 jam):\n\n";
        $message .= $link_reset . "\n\n";
        $message .= "Jika Anda tidak meminta ini, abaikan saja email ini.\n\nTerima kasih.";
        
        $headers = "From: noreply@cuantrack.com";
        
        if (mail($to, $subject, $message, $headers)) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('Berhasil!', 'Link reset kata sandi telah dikirim ke email Anda. Silakan cek Inbox atau folder Spam.', 'success');
                });
            </script>";
        } else {
            echo "<script>alert('Gagal mengirim email. Pastikan server mendukung pengiriman SMTP.');</script>";
        }
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('Error', 'Email tidak ditemukan di sistem kami!', 'error');
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Sandi - CuanTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login.css"> <!-- Pakai CSS login agar desain senada -->
</head>
<body class="login-bg">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card login-card border-0 shadow-lg p-4 p-md-5">
            <h4 class="fw-bold text-center mb-3">Lupa Kata Sandi</h4>
            <p class="text-muted text-center small mb-4">Masukkan email Anda yang terdaftar, kami akan mengirimkan link untuk membuat kata sandi baru.</p>
            
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Email Anda</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@gmail.com" required>
                </div>
                <button type="submit" name="btn_reset" class="btn btn-primary w-100 fw-bold py-2">Kirim Link Reset</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small text-muted">Kembali ke halaman Masuk</a>
            </div>
        </div>
    </div>
</body>
</html>