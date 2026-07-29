<?php
$host     = "cuantrack-db.mysql.database.azure.com"; // Host database Azure kamu
$username = "admincuan";                             // Username database Azure
$password = "CuanTrack123!";                  // Ganti dengan password Azure MySQL kamu yang asli
$database = "cuan_track";                            // Nama database yang kita buat di HeidiSQL tadi

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>