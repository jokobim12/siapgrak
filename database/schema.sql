-- ============================================
-- SIAPGRAK - Database Schema (Self-Manage Version)
-- Sistem Organisasi Materi Kuliah
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables
DROP TABLE IF EXISTS pengumpulan_tugas;
DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS materi;
DROP TABLE IF EXISTS pertemuan;
DROP TABLE IF EXISTS jadwal;
DROP TABLE IF EXISTS mata_kuliah;
DROP TABLE IF EXISTS semester;
DROP TABLE IF EXISTS notifikasi;
DROP TABLE IF EXISTS mahasiswa;
DROP TABLE IF EXISTS admin;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Tabel Admin
-- ============================================
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (Password: password)
INSERT INTO admin (username, password, nama, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@siapgrak.test');

-- ============================================
-- Tabel Mahasiswa
-- ============================================
CREATE TABLE mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    google_id VARCHAR(100) UNIQUE,
    foto VARCHAR(255),
    angkatan INT NOT NULL,
    semester_aktif INT DEFAULT 1,
    gdrive_folder_id VARCHAR(100) COMMENT 'Root folder ID di Google Drive mahasiswa',
    access_token TEXT COMMENT 'Google OAuth access token',
    refresh_token TEXT COMMENT 'Google OAuth refresh token',
    token_expires_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nim (nim),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Semester (Self-Manage by Mahasiswa)
-- ============================================
CREATE TABLE semester (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    INDEX idx_mahasiswa (mahasiswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Mata Kuliah (Self-Manage by Mahasiswa)
-- ============================================
CREATE TABLE mata_kuliah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    semester_id INT NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    kode_mk VARCHAR(20),
    dosen VARCHAR(100),
    folder_gdrive_id VARCHAR(100) COMMENT 'Folder ID di Google Drive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semester(id) ON DELETE CASCADE,
    INDEX idx_mahasiswa (mahasiswa_id),
    INDEX idx_semester (semester_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Pertemuan (P1-P18)
-- ============================================
CREATE TABLE pertemuan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mata_kuliah_id INT NOT NULL,
    nomor_pertemuan INT NOT NULL COMMENT '1-18',
    judul VARCHAR(200),
    deskripsi TEXT,
    folder_gdrive_id VARCHAR(100) COMMENT 'Folder ID di Google Drive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    INDEX idx_mk (mata_kuliah_id),
    UNIQUE KEY uk_pertemuan (mata_kuliah_id, nomor_pertemuan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Materi
-- ============================================
CREATE TABLE materi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pertemuan_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    nama_file VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(50) NOT NULL,
    ukuran_file INT COMMENT 'Ukuran dalam bytes',
    file_gdrive_id VARCHAR(100) NOT NULL,
    file_gdrive_url VARCHAR(500),
    uploaded_by INT NOT NULL COMMENT 'mahasiswa_id',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pertemuan_id) REFERENCES pertemuan(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    INDEX idx_pertemuan (pertemuan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Tugas
-- ============================================
CREATE TABLE tugas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pertemuan_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME NOT NULL,
    file_gdrive_id VARCHAR(100),
    file_gdrive_url VARCHAR(500),
    nama_file VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pertemuan_id) REFERENCES pertemuan(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES mahasiswa(id) ON DELETE SET NULL,
    INDEX idx_pertemuan (pertemuan_id),
    INDEX idx_deadline (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Pengumpulan Tugas
-- ============================================
CREATE TABLE pengumpulan_tugas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tugas_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file_gdrive_id VARCHAR(100) NOT NULL,
    file_gdrive_url VARCHAR(500),
    catatan TEXT,
    nilai INT,
    feedback TEXT,
    status ENUM('submitted', 'graded', 'late') DEFAULT 'submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at DATETIME,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    UNIQUE KEY uk_submission (tugas_id, mahasiswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Jadwal
-- ============================================
CREATE TABLE jadwal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mata_kuliah_id INT NOT NULL,
    hari ENUM('senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    INDEX idx_hari (hari)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Notifikasi
-- ============================================
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    link VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    INDEX idx_mahasiswa (mahasiswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabel Progress Mahasiswa
-- ============================================
CREATE TABLE progress_mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    mata_kuliah_id INT NOT NULL,
    pertemuan_selesai INT DEFAULT 0,
    materi_selesai INT DEFAULT 0,
    last_accessed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE,
    UNIQUE KEY uk_progress (mahasiswa_id, mata_kuliah_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
