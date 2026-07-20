<?php
// Pengaturan default Laragon
$host       = "localhost";
$user       = "root";
$password   = ""; 
$database   = "cuan_track"; 

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $password, $database);

// Mengecek koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset untuk memastikan teks dan password tersimpan dengan benar
mysqli_set_charset($koneksi, 'utf8mb4');
?>