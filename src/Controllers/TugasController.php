<?php

/**
 * Tugas Controller
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/Helpers/GoogleDriveHelper.php';
require_once ROOT_PATH . '/src/Controllers/AuthController.php';

use App\Helpers\GoogleDriveHelper;

class TugasController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Daftar semua tugas mahasiswa
     */
    public function index()
    {
        $user = auth();
        $filter = $_GET['filter'] ?? 'all'; // all, pending, submitted, late

        $query = "SELECT t.*, p.nomor_pertemuan, mk.nama_mk, mk.kode_mk, k.nama_kelas,
                         DATEDIFF(t.deadline, NOW()) as sisa_hari,
                         pt.id as submission_id, pt.status as submission_status, pt.nilai,
                         pt.submitted_at
                  FROM tugas t
                  JOIN pertemuan p ON p.id = t.pertemuan_id
                  JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
                  JOIN kelas k ON k.id = mk.kelas_id
                  JOIN kelas_mahasiswa km ON km.kelas_id = k.id
                  LEFT JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id AND pt.mahasiswa_id = ?
                  WHERE km.mahasiswa_id = ?";

        $params = [$user['id'], $user['id']];

        switch ($filter) {
            case 'pending':
                $query .= " AND pt.id IS NULL AND t.deadline >= NOW()";
                break;
            case 'submitted':
                $query .= " AND pt.id IS NOT NULL";
                break;
            case 'late':
                $query .= " AND pt.id IS NULL AND t.deadline < NOW()";
                break;
        }

        $query .= " ORDER BY t.deadline ASC";

        $tugasList = $this->db->fetchAll($query, $params);

        // Stats
        $stats = [
            'total' => $this->db->count(
                'tugas t 
                JOIN pertemuan p ON p.id = t.pertemuan_id 
                JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
                JOIN kelas_mahasiswa km ON km.kelas_id = mk.kelas_id',
                'km.mahasiswa_id = ?',
                [$user['id']]
            ),
            'pending' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM tugas t
                 JOIN pertemuan p ON p.id = t.pertemuan_id
                 JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
                 JOIN kelas_mahasiswa km ON km.kelas_id = mk.kelas_id
                 LEFT JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id AND pt.mahasiswa_id = ?
                 WHERE km.mahasiswa_id = ? AND pt.id IS NULL AND t.deadline >= NOW()",
                [$user['id'], $user['id']]
            )['count'],
            'submitted' => $this->db->count('pengumpulan_tugas', 'mahasiswa_id = ?', [$user['id']]),
            'late' => $this->db->fetch(
                "SELECT COUNT(*) as count FROM tugas t
                 JOIN pertemuan p ON p.id = t.pertemuan_id
                 JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
                 JOIN kelas_mahasiswa km ON km.kelas_id = mk.kelas_id
                 LEFT JOIN pengumpulan_tugas pt ON pt.tugas_id = t.id AND pt.mahasiswa_id = ?
                 WHERE km.mahasiswa_id = ? AND pt.id IS NULL AND t.deadline < NOW()",
                [$user['id'], $user['id']]
            )['count']
        ];

        view('tugas.index', [
            'tugasList' => $tugasList,
            'stats' => $stats,
            'filter' => $filter
        ]);
    }

    /**
     * Detail tugas
     */
    public function detail()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('tugas');
        }

        $tugas = $this->db->fetch(
            "SELECT t.*, p.nomor_pertemuan, p.mata_kuliah_id,
                    mk.nama_mk, mk.kode_mk, mk.dosen, mk.kelas_id,
                    k.nama_kelas, s.nama as semester_nama,
                    DATEDIFF(t.deadline, NOW()) as sisa_hari
             FROM tugas t
             JOIN pertemuan p ON p.id = t.pertemuan_id
             JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN semester s ON s.id = k.semester_id
             WHERE t.id = ?",
            [$id]
        );

        if (!$tugas) {
            redirect('tugas');
        }

        // Check access
        $hasAccess = $this->db->fetch(
            "SELECT 1 FROM kelas_mahasiswa WHERE kelas_id = ? AND mahasiswa_id = ?",
            [$tugas['kelas_id'], $user['id']]
        );

        if (!$hasAccess) {
            flash('error', 'Anda tidak memiliki akses');
            redirect('tugas');
        }

        // Get submission
        $submission = $this->db->fetch(
            "SELECT * FROM pengumpulan_tugas WHERE tugas_id = ? AND mahasiswa_id = ?",
            [$id, $user['id']]
        );

        view('tugas.detail', [
            'tugas' => $tugas,
            'submission' => $submission
        ]);
    }

    /**
     * Submit tugas
     */
    public function submit()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tugas');
        }

        $tugasId = $_POST['tugas_id'] ?? null;
        $catatan = sanitize($_POST['catatan'] ?? '');

        if (!$tugasId || !isset($_FILES['file'])) {
            flash('error', 'Data tidak lengkap');
            redirect("tugas/detail?id=$tugasId");
        }

        // Get tugas
        $tugas = $this->db->fetch(
            "SELECT t.*, p.folder_gdrive_id as pertemuan_folder_id, p.mata_kuliah_id
             FROM tugas t
             JOIN pertemuan p ON p.id = t.pertemuan_id
             WHERE t.id = ?",
            [$tugasId]
        );

        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        // Check if already submitted
        $existing = $this->db->fetch(
            "SELECT id FROM pengumpulan_tugas WHERE tugas_id = ? AND mahasiswa_id = ?",
            [$tugasId, $user['id']]
        );

        if ($existing) {
            flash('error', 'Anda sudah mengumpulkan tugas ini');
            redirect("tugas/detail?id=$tugasId");
        }

        // Get tokens and upload to Drive
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);

        if (!$tokens) {
            flash('error', 'Token tidak valid. Silakan login ulang.');
            redirect('login');
        }

        $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);
        $mahasiswa = $this->db->fetch("SELECT * FROM mahasiswa WHERE id = ?", [$user['id']]);

        // Create submissions folder if not exists
        $folderId = $tugas['pertemuan_folder_id'] ?? $mahasiswa['gdrive_folder_id'];

        $uploadResult = $driveHelper->uploadFromPost('file', $folderId);

        if (!$uploadResult['success']) {
            flash('error', 'Gagal upload: ' . $uploadResult['error']);
            redirect("tugas/detail?id=$tugasId");
        }

        // Determine status
        $status = 'submitted';
        if (strtotime($tugas['deadline']) < time()) {
            $status = 'late';
        }

        // Save submission
        $this->db->insert('pengumpulan_tugas', [
            'tugas_id' => $tugasId,
            'mahasiswa_id' => $user['id'],
            'nama_file' => $_FILES['file']['name'],
            'file_gdrive_id' => $uploadResult['id'],
            'file_gdrive_url' => $uploadResult['url'],
            'catatan' => $catatan,
            'status' => $status
        ]);

        // Update progress
        $this->db->query(
            "UPDATE progress_mahasiswa 
             SET tugas_selesai = tugas_selesai + 1
             WHERE mahasiswa_id = ? AND mata_kuliah_id = ?",
            [$user['id'], $tugas['mata_kuliah_id']]
        );

        // Create notification
        $this->db->insert('notifikasi', [
            'mahasiswa_id' => $user['id'],
            'judul' => 'Tugas Berhasil Dikumpulkan',
            'pesan' => "Tugas '{$tugas['judul']}' telah berhasil dikumpulkan.",
            'tipe' => 'success',
            'link' => "/tugas/detail?id=$tugasId"
        ]);

        flash('success', 'Tugas berhasil dikumpulkan!');
        redirect("tugas/detail?id=$tugasId");
    }

    /**
     * Buat tugas baru (untuk reminder pribadi)
     */
    public function create()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tugas');
        }

        $pertemuanId = $_POST['pertemuan_id'] ?? null;
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = $_POST['deadline'] ?? null;

        if (!$pertemuanId || !$judul || !$deadline) {
            flash('error', 'Data tidak lengkap');
            redirect("pertemuan?id=$pertemuanId");
        }

        $this->db->insert('tugas', [
            'pertemuan_id' => $pertemuanId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'deadline' => $deadline,
            'created_by' => $user['id']
        ]);

        // Create reminder notification
        $pertemuan = $this->db->fetch("SELECT mata_kuliah_id FROM pertemuan WHERE id = ?", [$pertemuanId]);

        $this->db->insert('notifikasi', [
            'mahasiswa_id' => $user['id'],
            'judul' => 'Tugas Baru Ditambahkan',
            'pesan' => "Tugas '$judul' dengan deadline " . formatDateTime($deadline) . " telah ditambahkan.",
            'tipe' => 'info',
            'link' => "/pertemuan?id=$pertemuanId"
        ]);

        flash('success', 'Tugas berhasil ditambahkan');
        redirect("pertemuan?id=$pertemuanId");
    }
}
