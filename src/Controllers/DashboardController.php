<?php

/**
 * Dashboard Controller
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class DashboardController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Halaman dashboard utama
     */
    public function index()
    {
        $user = auth();

        // Get semester aktif
        $semesterAktif = $this->db->fetch(
            "SELECT * FROM semester WHERE is_active = 1 LIMIT 1"
        );

        // Get kelas mahasiswa di semester aktif
        $kelasAktif = [];
        if ($semesterAktif) {
            $kelasAktif = $this->db->fetchAll(
                "SELECT k.*, s.nama as semester_nama 
                 FROM kelas k 
                 JOIN semester s ON s.id = k.semester_id
                 JOIN kelas_mahasiswa km ON km.kelas_id = k.id
                 WHERE km.mahasiswa_id = ? AND k.semester_id = ?",
                [$user['id'], $semesterAktif['id']]
            );
        }

        // Get mata kuliah dari kelas aktif
        $mataKuliahList = [];
        foreach ($kelasAktif as $kelas) {
            $mataKuliah = $this->db->fetchAll(
                "SELECT mk.*, 
                        (SELECT COUNT(*) FROM pertemuan WHERE mata_kuliah_id = mk.id) as total_pertemuan,
                        (SELECT COUNT(*) FROM materi m JOIN pertemuan p ON p.id = m.pertemuan_id WHERE p.mata_kuliah_id = mk.id) as total_materi
                 FROM mata_kuliah mk 
                 WHERE mk.kelas_id = ?",
                [$kelas['id']]
            );
            $mataKuliahList = array_merge($mataKuliahList, $mataKuliah);
        }

        // Get tugas mendekati deadline
        $tugasDeadline = $this->db->fetchAll(
            "SELECT t.*, p.nomor_pertemuan, mk.nama_mk, mk.kelas_id,
                    DATEDIFF(t.deadline, NOW()) as sisa_hari,
                    (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = t.id AND mahasiswa_id = ?) as sudah_submit
             FROM tugas t
             JOIN pertemuan p ON p.id = t.pertemuan_id
             JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN kelas_mahasiswa km ON km.kelas_id = k.id
             WHERE km.mahasiswa_id = ? AND t.deadline >= NOW()
             ORDER BY t.deadline ASC
             LIMIT 5",
            [$user['id'], $user['id']]
        );

        // Get notifikasi terbaru
        $notifikasi = $this->db->fetchAll(
            "SELECT * FROM notifikasi WHERE mahasiswa_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        );

        // Get unread notification count
        $unreadCount = $this->db->count('notifikasi', 'mahasiswa_id = ? AND is_read = 0', [$user['id']]);

        // Get jadwal hari ini
        $hariIni = strtolower(date('l'));
        $hariMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        $hari = $hariMap[$hariIni] ?? 'senin';

        $jadwalHariIni = $this->db->fetchAll(
            "SELECT j.*, mk.nama_mk, mk.dosen, k.nama_kelas
             FROM jadwal j
             JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
             JOIN kelas k ON k.id = j.kelas_id
             JOIN kelas_mahasiswa km ON km.kelas_id = k.id
             WHERE km.mahasiswa_id = ? AND j.hari = ?
             ORDER BY j.jam_mulai ASC",
            [$user['id'], $hari]
        );

        // Get progress overview
        $progressStats = $this->db->fetch(
            "SELECT 
                COUNT(DISTINCT mk.id) as total_matkul,
                COALESCE(SUM(pm.pertemuan_selesai), 0) as pertemuan_selesai,
                COALESCE(SUM(pm.tugas_selesai), 0) as tugas_selesai
             FROM mata_kuliah mk
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN kelas_mahasiswa km ON km.kelas_id = k.id
             LEFT JOIN progress_mahasiswa pm ON pm.mata_kuliah_id = mk.id AND pm.mahasiswa_id = ?
             WHERE km.mahasiswa_id = ?",
            [$user['id'], $user['id']]
        );

        view('dashboard.index', [
            'user' => $user,
            'semesterAktif' => $semesterAktif,
            'kelasAktif' => $kelasAktif,
            'mataKuliahList' => $mataKuliahList,
            'tugasDeadline' => $tugasDeadline,
            'notifikasi' => $notifikasi,
            'unreadCount' => $unreadCount,
            'jadwalHariIni' => $jadwalHariIni,
            'progressStats' => $progressStats
        ]);
    }

    /**
     * Mark notifikasi as read
     */
    public function readNotification()
    {
        $id = $_POST['id'] ?? null;
        $user = auth();

        if ($id) {
            $this->db->update(
                'notifikasi',
                ['is_read' => 1],
                'id = ? AND mahasiswa_id = ?',
                [$id, $user['id']]
            );
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function readAllNotifications()
    {
        $user = auth();
        $this->db->update(
            'notifikasi',
            ['is_read' => 1],
            'mahasiswa_id = ?',
            [$user['id']]
        );

        echo json_encode(['success' => true]);
    }
}
