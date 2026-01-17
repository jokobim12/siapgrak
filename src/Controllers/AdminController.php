<?php

/**
 * Admin Controller
 * Panel untuk mengelola data akademik
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = db();

        // Check if admin is logged in
        if (!isset($_SESSION['admin'])) {
            redirect('admin/login');
        }
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'mahasiswa' => $this->db->count('mahasiswa'),
            'semester' => $this->db->count('semester'),
            'kelas' => $this->db->count('kelas'),
            'mata_kuliah' => $this->db->count('mata_kuliah'),
            'materi' => $this->db->count('materi'),
            'tugas' => $this->db->count('tugas')
        ];

        $recentMahasiswa = $this->db->fetchAll(
            "SELECT * FROM mahasiswa ORDER BY created_at DESC LIMIT 5"
        );

        view('admin.dashboard', [
            'stats' => $stats,
            'recentMahasiswa' => $recentMahasiswa
        ]);
    }

    /**
     * Kelola Semester
     */
    public function semester()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $this->db->insert('semester', [
                    'nama' => sanitize($_POST['nama']),
                    'tahun_ajaran' => sanitize($_POST['tahun_ajaran']),
                    'periode' => $_POST['periode'],
                    'nomor_semester' => intval($_POST['nomor_semester']),
                    'tanggal_mulai' => $_POST['tanggal_mulai'],
                    'tanggal_selesai' => $_POST['tanggal_selesai'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);
                flash('success', 'Semester berhasil ditambahkan');
            } elseif ($action === 'update') {
                $id = $_POST['id'];
                $this->db->update('semester', [
                    'nama' => sanitize($_POST['nama']),
                    'tahun_ajaran' => sanitize($_POST['tahun_ajaran']),
                    'periode' => $_POST['periode'],
                    'nomor_semester' => intval($_POST['nomor_semester']),
                    'tanggal_mulai' => $_POST['tanggal_mulai'],
                    'tanggal_selesai' => $_POST['tanggal_selesai'],
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ], 'id = ?', [$id]);
                flash('success', 'Semester berhasil diupdate');
            } elseif ($action === 'delete') {
                $this->db->delete('semester', 'id = ?', [$_POST['id']]);
                flash('success', 'Semester berhasil dihapus');
            } elseif ($action === 'set_active') {
                $this->db->query("UPDATE semester SET is_active = 0");
                $this->db->update('semester', ['is_active' => 1], 'id = ?', [$_POST['id']]);
                flash('success', 'Semester aktif berhasil diubah');
            }

            redirect('admin/semester');
        }

        $semesters = $this->db->fetchAll("SELECT * FROM semester ORDER BY tanggal_mulai DESC");
        view('admin.semester', ['semesters' => $semesters]);
    }

    /**
     * Kelola Kelas
     */
    public function kelas()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $this->db->insert('kelas', [
                    'nama_kelas' => sanitize($_POST['nama_kelas']),
                    'semester_id' => intval($_POST['semester_id']),
                    'deskripsi' => sanitize($_POST['deskripsi'] ?? '')
                ]);
                flash('success', 'Kelas berhasil ditambahkan');
            } elseif ($action === 'update') {
                $this->db->update('kelas', [
                    'nama_kelas' => sanitize($_POST['nama_kelas']),
                    'semester_id' => intval($_POST['semester_id']),
                    'deskripsi' => sanitize($_POST['deskripsi'] ?? '')
                ], 'id = ?', [$_POST['id']]);
                flash('success', 'Kelas berhasil diupdate');
            } elseif ($action === 'delete') {
                $this->db->delete('kelas', 'id = ?', [$_POST['id']]);
                flash('success', 'Kelas berhasil dihapus');
            }

            redirect('admin/kelas');
        }

        $kelasList = $this->db->fetchAll(
            "SELECT k.*, s.nama as semester_nama,
                    (SELECT COUNT(*) FROM kelas_mahasiswa WHERE kelas_id = k.id) as total_mahasiswa,
                    (SELECT COUNT(*) FROM mata_kuliah WHERE kelas_id = k.id) as total_matkul
             FROM kelas k
             JOIN semester s ON s.id = k.semester_id
             ORDER BY s.tanggal_mulai DESC, k.nama_kelas ASC"
        );

        $semesters = $this->db->fetchAll("SELECT * FROM semester ORDER BY tanggal_mulai DESC");

        view('admin.kelas', [
            'kelasList' => $kelasList,
            'semesters' => $semesters
        ]);
    }

    /**
     * Kelola Mata Kuliah
     */
    public function mataKuliah()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $mkId = $this->db->insert('mata_kuliah', [
                    'kode_mk' => sanitize($_POST['kode_mk']),
                    'nama_mk' => sanitize($_POST['nama_mk']),
                    'kelas_id' => intval($_POST['kelas_id']),
                    'dosen' => sanitize($_POST['dosen'] ?? ''),
                    'sks' => intval($_POST['sks'] ?? 3),
                    'deskripsi' => sanitize($_POST['deskripsi'] ?? '')
                ]);

                // Auto-create pertemuan P1-P18
                for ($i = 1; $i <= 18; $i++) {
                    $this->db->insert('pertemuan', [
                        'mata_kuliah_id' => $mkId,
                        'nomor_pertemuan' => $i,
                        'judul' => "Pertemuan $i"
                    ]);
                }

                flash('success', 'Mata Kuliah berhasil ditambahkan');
            } elseif ($action === 'update') {
                $this->db->update('mata_kuliah', [
                    'kode_mk' => sanitize($_POST['kode_mk']),
                    'nama_mk' => sanitize($_POST['nama_mk']),
                    'kelas_id' => intval($_POST['kelas_id']),
                    'dosen' => sanitize($_POST['dosen'] ?? ''),
                    'sks' => intval($_POST['sks'] ?? 3),
                    'deskripsi' => sanitize($_POST['deskripsi'] ?? '')
                ], 'id = ?', [$_POST['id']]);
                flash('success', 'Mata Kuliah berhasil diupdate');
            } elseif ($action === 'delete') {
                $this->db->delete('mata_kuliah', 'id = ?', [$_POST['id']]);
                flash('success', 'Mata Kuliah berhasil dihapus');
            }

            redirect('admin/mata-kuliah');
        }

        $mataKuliahList = $this->db->fetchAll(
            "SELECT mk.*, k.nama_kelas, s.nama as semester_nama,
                    (SELECT COUNT(*) FROM pertemuan WHERE mata_kuliah_id = mk.id) as total_pertemuan,
                    (SELECT COUNT(*) FROM materi m JOIN pertemuan p ON p.id = m.pertemuan_id WHERE p.mata_kuliah_id = mk.id) as total_materi
             FROM mata_kuliah mk
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN semester s ON s.id = k.semester_id
             ORDER BY s.tanggal_mulai DESC, k.nama_kelas ASC, mk.nama_mk ASC"
        );

        $kelasList = $this->db->fetchAll(
            "SELECT k.*, s.nama as semester_nama 
             FROM kelas k 
             JOIN semester s ON s.id = k.semester_id 
             ORDER BY s.tanggal_mulai DESC"
        );

        view('admin.mata_kuliah', [
            'mataKuliahList' => $mataKuliahList,
            'kelasList' => $kelasList
        ]);
    }

    /**
     * Kelola Jadwal
     */
    public function jadwal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $this->db->insert('jadwal', [
                    'kelas_id' => intval($_POST['kelas_id']),
                    'mata_kuliah_id' => intval($_POST['mata_kuliah_id']),
                    'hari' => $_POST['hari'],
                    'jam_mulai' => $_POST['jam_mulai'],
                    'jam_selesai' => $_POST['jam_selesai'],
                    'ruangan' => sanitize($_POST['ruangan'] ?? '')
                ]);
                flash('success', 'Jadwal berhasil ditambahkan');
            } elseif ($action === 'update') {
                $this->db->update('jadwal', [
                    'kelas_id' => intval($_POST['kelas_id']),
                    'mata_kuliah_id' => intval($_POST['mata_kuliah_id']),
                    'hari' => $_POST['hari'],
                    'jam_mulai' => $_POST['jam_mulai'],
                    'jam_selesai' => $_POST['jam_selesai'],
                    'ruangan' => sanitize($_POST['ruangan'] ?? '')
                ], 'id = ?', [$_POST['id']]);
                flash('success', 'Jadwal berhasil diupdate');
            } elseif ($action === 'delete') {
                $this->db->delete('jadwal', 'id = ?', [$_POST['id']]);
                flash('success', 'Jadwal berhasil dihapus');
            }

            redirect('admin/jadwal');
        }

        $jadwalList = $this->db->fetchAll(
            "SELECT j.*, mk.nama_mk, mk.kode_mk, k.nama_kelas
             FROM jadwal j
             JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
             JOIN kelas k ON k.id = j.kelas_id
             ORDER BY k.nama_kelas ASC, 
                      FIELD(j.hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'),
                      j.jam_mulai ASC"
        );

        $kelasList = $this->db->fetchAll(
            "SELECT k.*, s.nama as semester_nama 
             FROM kelas k 
             JOIN semester s ON s.id = k.semester_id 
             ORDER BY s.tanggal_mulai DESC"
        );

        $mataKuliahList = $this->db->fetchAll(
            "SELECT mk.*, k.nama_kelas 
             FROM mata_kuliah mk 
             JOIN kelas k ON k.id = mk.kelas_id 
             ORDER BY k.nama_kelas, mk.nama_mk"
        );

        view('admin.jadwal', [
            'jadwalList' => $jadwalList,
            'kelasList' => $kelasList,
            'mataKuliahList' => $mataKuliahList
        ]);
    }

    /**
     * Kelola Mahasiswa Kelas (assign mahasiswa ke kelas)
     */
    public function kelasMahasiswa()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'assign') {
                $kelasId = intval($_POST['kelas_id']);
                $mahasiswaId = intval($_POST['mahasiswa_id']);

                // Check if already assigned
                $exists = $this->db->fetch(
                    "SELECT 1 FROM kelas_mahasiswa WHERE kelas_id = ? AND mahasiswa_id = ?",
                    [$kelasId, $mahasiswaId]
                );

                if (!$exists) {
                    $this->db->insert('kelas_mahasiswa', [
                        'kelas_id' => $kelasId,
                        'mahasiswa_id' => $mahasiswaId
                    ]);
                    flash('success', 'Mahasiswa berhasil ditambahkan ke kelas');
                } else {
                    flash('error', 'Mahasiswa sudah terdaftar di kelas ini');
                }
            } elseif ($action === 'remove') {
                $this->db->delete('kelas_mahasiswa', 'id = ?', [$_POST['id']]);
                flash('success', 'Mahasiswa berhasil dihapus dari kelas');
            }

            redirect('admin/kelas-mahasiswa?kelas_id=' . ($_POST['kelas_id'] ?? ''));
        }

        $kelasId = $_GET['kelas_id'] ?? null;

        $kelasList = $this->db->fetchAll(
            "SELECT k.*, s.nama as semester_nama 
             FROM kelas k 
             JOIN semester s ON s.id = k.semester_id 
             ORDER BY s.tanggal_mulai DESC"
        );

        $kelasMahasiswa = [];
        $availableMahasiswa = [];

        if ($kelasId) {
            $kelasMahasiswa = $this->db->fetchAll(
                "SELECT km.*, m.nim, m.nama, m.email
                 FROM kelas_mahasiswa km
                 JOIN mahasiswa m ON m.id = km.mahasiswa_id
                 WHERE km.kelas_id = ?
                 ORDER BY m.nim ASC",
                [$kelasId]
            );

            $availableMahasiswa = $this->db->fetchAll(
                "SELECT * FROM mahasiswa 
                 WHERE id NOT IN (SELECT mahasiswa_id FROM kelas_mahasiswa WHERE kelas_id = ?)
                 ORDER BY nim ASC",
                [$kelasId]
            );
        }

        view('admin.kelas_mahasiswa', [
            'kelasList' => $kelasList,
            'kelasMahasiswa' => $kelasMahasiswa,
            'availableMahasiswa' => $availableMahasiswa,
            'selectedKelasId' => $kelasId
        ]);
    }

    /**
     * Admin Logout
     */
    public function logout()
    {
        unset($_SESSION['admin']);
        redirect('admin/login');
    }
}
