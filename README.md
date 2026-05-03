# 035_M.HamdiYahya_Tugas_Pertemuan_7
Ini adalah tugas pertemuan 7 menggunakan PHP

# CRUD Anggota Perpustakaan

Project sederhana CRUD (Create, Read, Update, Delete) untuk data anggota perpustakaan menggunakan PHP Native dan MySQL.

## Struktur Folder

perpustakaan/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
└── modules/
    └── anggota/
        ├── index.php
        ├── create.php
        ├── edit.php
        ├── delete.php
        └── uploads/

## SQL Dump Tabel Anggota

CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    kode_anggota VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telepon VARCHAR(15) NOT NULL,
    alamat TEXT NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    pekerjaan VARCHAR(50),
    tanggal_daftar DATE NOT NULL,
    status ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO anggota
(kode_anggota, nama, email, telepon, alamat, tanggal_lahir, jenis_kelamin, pekerjaan, tanggal_daftar, status)
VALUES
('AGT-001', 'Budi Santoso', 'budi@email.com', '081234567890', 'Jakarta', '2000-01-01', 'Laki-laki', 'Mahasiswa', CURDATE(), 'Aktif'),
('AGT-002', 'Siti Nurhaliza', 'siti@email.com', '081234567891', 'Bandung', '2001-02-02', 'Perempuan', 'Pelajar', CURDATE(), 'Aktif'),
('AGT-003', 'Ahmad Dhani', 'ahmad@email.com', '081234567892', 'Surabaya', '1999-03-03', 'Laki-laki', 'Musisi', CURDATE(), 'Aktif'),
('AGT-004', 'Dewi Lestari', 'dewi@email.com', '081234567893', 'Yogyakarta', '1998-04-04', 'Perempuan', 'Penulis', CURDATE(), 'Aktif'),
('AGT-005', 'Rizky Febian', 'rizky@email.com', '081234567894', 'Jakarta', '2002-05-05', 'Laki-laki', 'Penyanyi', CURDATE(), 'Nonaktif');
