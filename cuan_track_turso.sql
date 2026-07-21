-- Tabel rencana_keuangan
CREATE TABLE IF NOT EXISTS "rencana_keuangan" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "pendapatan" INTEGER DEFAULT 0,
  "pengeluaran" INTEGER DEFAULT 0,
  "target_nabung" INTEGER DEFAULT 0,
  "lama_nabung" INTEGER DEFAULT 10,
  "nama_barang" TEXT DEFAULT NULL,
  "harga_barang" INTEGER DEFAULT 0,
  "bulan_darurat" INTEGER DEFAULT 3,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO "rencana_keuangan" ("id", "user_id", "pendapatan", "pengeluaran", "target_nabung", "lama_nabung", "nama_barang", "harga_barang", "bulan_darurat", "created_at") VALUES
  (1, 1, 0, 0, 100000, 1, 'laptop', 25000000, 6, '2026-07-11 07:48:55');


-- Tabel transaksi
CREATE TABLE IF NOT EXISTS "transaksi" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "user_id" INTEGER NOT NULL,
  "jenis" TEXT NOT NULL DEFAULT 'Pengeluaran' CHECK ("jenis" IN ('Pengeluaran', 'Pemasukan')),
  "tanggal" TEXT NOT NULL,
  "jumlah" INTEGER NOT NULL DEFAULT 0,
  "kategori" TEXT NOT NULL,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO "transaksi" ("id", "user_id", "jenis", "tanggal", "jumlah", "kategori", "created_at") VALUES
  (2, 1, 'Pemasukan', '2026-07-16', 5000000, 'Lainnya', '2026-07-16 23:08:39'),
  (3, 1, 'Pengeluaran', '2026-07-16', 50000, 'Transportasi', '2026-07-16 23:47:48'),
  (4, 1, 'Pengeluaran', '2026-07-17', 50000, 'Kebutuhan Pokok', '2026-07-17 10:00:50'),
  (5, 1, 'Pengeluaran', '2026-07-17', 100000, 'Kebutuhan Pokok', '2026-07-17 10:13:16'),
  (10, 1, 'Pengeluaran', '2026-07-18', 50000, 'Investasi', '2026-07-18 03:44:07'),
  (11, 1, 'Pengeluaran', '2026-07-18', 50000, 'Lainnya', '2026-07-18 03:44:15'),
  (12, 1, 'Pengeluaran', '2026-07-18', 500000, 'Belanja', '2026-07-18 03:44:33'),
  (13, 1, 'Pengeluaran', '2026-07-18', 1000000, 'Hiburan', '2026-07-18 03:44:57'),
  (14, 1, 'Pemasukan', '2026-07-20', 5000000, 'Gaji', '2026-07-20 13:51:37'),
  (15, 1, 'Pengeluaran', '2026-07-20', 1500000, 'Impian: hp', '2026-07-20 14:56:43'),
  (16, 1, 'Pengeluaran', '2026-07-20', 500000, 'Belanja', '2026-07-20 14:58:53');


-- Tabel users
CREATE TABLE IF NOT EXISTS "users" (
  "id" INTEGER PRIMARY KEY AUTOINCREMENT,
  "nama_lengkap" TEXT NOT NULL,
  "email" TEXT NOT NULL UNIQUE,
  "foto" TEXT DEFAULT NULL,
  "password" TEXT NOT NULL,
  "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO "users" ("id", "nama_lengkap", "email", "foto", "password", "created_at") VALUES
  (1, 'Arull', 'arulsatriaji5@gmail.com', '6a5eb4f99ccb9.png', '$2y$10$bJXAX/Ggujae2v.d5X8j4uQng4xt7g7cMLvZ1RZiToi1knOPeMMFW', '2026-07-09 01:32:25'),
  (2, 'Arull', 'arulsatriaji160526@gmail.com', NULL, '$2y$10$k5yrwvG0RObogS.tOoKd8OONg726oEYDItXPwb35hmNJqETO3vPOa', '2026-07-09 01:35:31');