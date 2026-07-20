<?php
// Memanggil file koneksi
require 'koneksi.php';

function registrasi($data) {
    global $koneksi;

    $nama_lengkap = trim($data['nama_lengkap'] ?? '');
    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        return 0;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 0;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return 0;
    }
    mysqli_stmt_close($stmt);

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sss', $nama_lengkap, $email, $password_hash);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

function login($data) {
    global $koneksi;

    $email = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        return false;
    }

    $stmt = mysqli_prepare($koneksi, "SELECT id, nama_lengkap, password FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $nama_lengkap, $hash);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    mysqli_stmt_close($stmt);

    if (!password_verify($password, $hash)) {
        return false;
    }

    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $id;
    $_SESSION['nama_lengkap'] = $nama_lengkap;

    return true;
}

function simpan_rencana($data, $user_id) {
    global $koneksi;

    $pendapatan = !empty($data['pendapatan']) ? (int) str_replace('.', '', $data['pendapatan']) : 0;
    $pengeluaran = !empty($data['pengeluaran']) ? (int) str_replace('.', '', $data['pengeluaran']) : 0;
    $target_nabung = !empty($data['target_nabung']) ? (int) str_replace('.', '', $data['target_nabung']) : 0;
    $lama_nabung = !empty($data['lama_nabung']) ? (int) $data['lama_nabung'] : 10;
    $nama_barang = trim($data['nama_barang'] ?? '');
    $harga_barang = !empty($data['harga_barang']) ? (int) str_replace('.', '', $data['harga_barang']) : 0;
    $bulan_darurat = !empty($data['bulan_darurat']) ? (int) $data['bulan_darurat'] : 3;

    $stmt = mysqli_prepare($koneksi, "SELECT id FROM rencana_keuangan WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        $stmt = mysqli_prepare($koneksi, "UPDATE rencana_keuangan SET pendapatan = ?, pengeluaran = ?, target_nabung = ?, lama_nabung = ?, nama_barang = ?, harga_barang = ?, bulan_darurat = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, 'iiiisiii', $pendapatan, $pengeluaran, $target_nabung, $lama_nabung, $nama_barang, $harga_barang, $bulan_darurat, $user_id);
    } else {
        mysqli_stmt_close($stmt);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO rencana_keuangan (user_id, pendapatan, pengeluaran, target_nabung, lama_nabung, nama_barang, harga_barang, bulan_darurat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiiisii', $user_id, $pendapatan, $pengeluaran, $target_nabung, $lama_nabung, $nama_barang, $harga_barang, $bulan_darurat);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

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
            'kategori' => 'Belanja'
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

function get_rencana($user_id) {
    global $koneksi;

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM rencana_keuangan WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ?: [];
}

function simpan_transaksi($data, $user_id) {
    global $koneksi;

    $jenis = trim($data['jenis'] ?? '');
    $tanggal = trim($data['tanggal'] ?? '');
    $jumlah = !empty($data['jumlah']) ? (int) str_replace('.', '', $data['jumlah']) : 0;
    $kategori = trim($data['kategori'] ?? '');

    if (!in_array($jenis, ['Pemasukan', 'Pengeluaran'], true) || empty($tanggal) || $jumlah <= 0 || empty($kategori)) {
        return 0;
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO transaksi (user_id, jenis, tanggal, jumlah, kategori) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issis', $user_id, $jenis, $tanggal, $jumlah, $kategori);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

function get_ringkasan_bulan_ini($user_id) {
    global $koneksi;
    $bulan_ini = date('m');
    $tahun_ini = date('Y');

    $stmt = mysqli_prepare($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND jenis = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $masuk = 0;
    $keluar = 0;

    $jenis_masuk = 'Pemasukan';
    mysqli_stmt_bind_param($stmt, 'isss', $user_id, $jenis_masuk, $bulan_ini, $tahun_ini);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total_masuk);
    if (mysqli_stmt_fetch($stmt) && $total_masuk !== null) {
        $masuk = (int) $total_masuk;
    }

    mysqli_stmt_free_result($stmt);
    $jenis_keluar = 'Pengeluaran';
    mysqli_stmt_bind_param($stmt, 'isss', $user_id, $jenis_keluar, $bulan_ini, $tahun_ini);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total_keluar);
    if (mysqli_stmt_fetch($stmt) && $total_keluar !== null) {
        $keluar = (int) $total_keluar;
    }

    mysqli_stmt_close($stmt);

    return [
        'pemasukan' => $masuk,
        'pengeluaran' => $keluar,
        'selisih' => $masuk - $keluar,
    ];
}

function get_riwayat($user_id, $filter_jenis = '', $keyword = '') {
    global $koneksi;

    $sql = "SELECT * FROM transaksi WHERE user_id = ?";
    $types = 'i';
    $params = [$user_id];

    if (!empty($filter_jenis) && $filter_jenis !== 'Semua') {
        $sql .= " AND jenis = ?";
        $types .= 's';
        $params[] = $filter_jenis;
    }

    if (!empty($keyword)) {
        $likeKeyword = "%{$keyword}%";
        $sql .= " AND (kategori LIKE ? OR tanggal LIKE ?)";
        $types .= 'ss';
        $params[] = $likeKeyword;
        $params[] = $likeKeyword;
    }

    $sql .= " ORDER BY tanggal DESC, id DESC";
    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $rows;
}

function hapus_transaksi($id, $user_id) {
    global $koneksi;

    $id = (int) $id;
    $stmt = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

// ==========================================
// FUNGSI AMBIL 1 TRANSAKSI UNTUK DIEDIT
// ==========================================
function get_transaksi_by_id($id, $user_id) {
    global $koneksi;
    $id = (int)$id;
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

// ==========================================
// FUNGSI UPDATE PROFIL (NAMA LENGKAP)
// ==========================================
function update_profil($data, $user_id) {
    global $koneksi;
    $nama_baru = trim($data['nama_lengkap_baru'] ?? '');

    if (empty($nama_baru)) {
        return "Nama lengkap tidak boleh kosong!";
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama_lengkap = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $nama_baru, $user_id);
    mysqli_stmt_execute($stmt);
    $sukses = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($sukses >= 0) {
        $_SESSION['nama_lengkap'] = $nama_baru; // Update nama di session agar langsung berubah di web
        return "SUKSES";
    } else {
        return "Gagal memperbarui nama di database!";
    }
}


// ==========================================
// FUNGSI UPDATE / SIMPAN PERUBAHAN TRANSAKSI
// ==========================================
function update_transaksi($data, $user_id) {
    global $koneksi;
    $id       = (int)($data['id_transaksi'] ?? 0);
    $jenis    = trim($data['jenis'] ?? '');
    $tanggal  = trim($data['tanggal'] ?? '');
    $jumlah   = !empty($data['jumlah']) ? (int)str_replace('.', '', $data['jumlah']) : 0;
    $kategori = trim($data['kategori'] ?? '');

    if ($id <= 0 || !in_array($jenis, ['Pemasukan', 'Pengeluaran'], true) || empty($tanggal) || $jumlah <= 0 || empty($kategori)) {
        return -1; // Gagal validasi
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE transaksi SET jenis = ?, tanggal = ?, jumlah = ?, kategori = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ssisii', $jenis, $tanggal, $jumlah, $kategori, $id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    // Mengembalikan >= 0 artinya sukses (walau tidak ada angka yang diubah user)
    return $result;
}

// ==========================================
// FUNGSI ANALISA: STATISTIK KATEGORI BULAN INI
// ==========================================
function get_stat_kategori_bulan_ini($user_id) {
    global $koneksi;
    $bulan_ini = date('m');
    $tahun_ini = date('Y');
    
    $stmt = mysqli_prepare($koneksi, "SELECT kategori, SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND jenis = 'Pengeluaran' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ? GROUP BY kategori ORDER BY total DESC");
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $bulan_ini, $tahun_ini);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

// ==========================================
// FUNGSI ANALISA: ARUS KAS 6 BULAN TERAKHIR
// ==========================================
function get_stat_6_bulan($user_id) {
    global $koneksi;
    $data = ['labels' => [], 'pemasukan' => [], 'pengeluaran' => []];
    
    for ($i = 5; $i >= 0; $i--) {
        $bulan = date('m', strtotime("-$i month"));
        $tahun = date('Y', strtotime("-$i month"));
        $nama_bulan = date('M', strtotime("-$i month"));
        
        $data['labels'][] = $nama_bulan;
        
        // Pemasukan
        $stmt = mysqli_prepare($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND jenis = 'Pemasukan' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $bulan, $tahun);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total_masuk);
        mysqli_stmt_fetch($stmt);
        $data['pemasukan'][] = $total_masuk ? (int)$total_masuk : 0;
        mysqli_stmt_close($stmt);
        
        // Pengeluaran
        $stmt = mysqli_prepare($koneksi, "SELECT SUM(jumlah) as total FROM transaksi WHERE user_id = ? AND jenis = 'Pengeluaran' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
        mysqli_stmt_bind_param($stmt, 'iss', $user_id, $bulan, $tahun);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total_keluar);
        mysqli_stmt_fetch($stmt);
        $data['pengeluaran'][] = $total_keluar ? (int)$total_keluar : 0;
        mysqli_stmt_close($stmt);
    }
    return $data;
}

// ==========================================
// FUNGSI GANTI KATA SANDI PENGGUNA
// ==========================================
function ganti_password($data, $user_id) {
    global $koneksi;

    $sandi_lama     = $data['sandi_lama'] ?? '';
    $sandi_baru     = $data['sandi_baru'] ?? '';
    $konfirmasi     = $data['konfirmasi_sandi'] ?? '';

    if (empty($sandi_lama) || empty($sandi_baru) || empty($konfirmasi)) {
        return "Semua kolom kata sandi wajib diisi!";
    }

    if ($sandi_baru !== $konfirmasi) {
        return "Konfirmasi kata sandi baru tidak cocok!";
    }

    if (strlen($sandi_baru) < 6) {
        return "Kata sandi baru minimal harus 6 karakter!";
    }

    // 1. Ambil hash kata sandi lama dari database
    $stmt = mysqli_prepare($koneksi, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hash_db);
    
    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        return "Pengguna tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);

    // 2. Verifikasi apakah kata sandi lama cocok
    if (!password_verify($sandi_lama, $hash_db)) {
        return "Kata sandi saat ini salah!";
    }

    // 3. Hash kata sandi baru & update ke database
    $hash_baru = password_hash($sandi_baru, PASSWORD_DEFAULT);
    $stmt_update = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update, 'si', $hash_baru, $user_id);
    mysqli_stmt_execute($stmt_update);
    $sukses = mysqli_stmt_affected_rows($stmt_update);
    mysqli_stmt_close($stmt_update);

    return ($sukses >= 0) ? "SUKSES" : "Gagal memperbarui kata sandi di database!";
}
?>

