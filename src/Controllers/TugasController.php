<?php

/**
 * Tugas Controller - Updated for Self-Manage
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
        $filter = $_GET['filter'] ?? 'all';

        $allTugas = $this->db->all('tugas');
        $allPertemuan = $this->db->all('pertemuan');
        $allMatkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        $allPengumpulan = $this->db->find('pengumpulan_tugas', ['mahasiswa_id' => $user['id']]);

        // Filter and enrich tugas
        $userMatkulIds = array_column($allMatkul, 'id');
        $userPertemuan = array_filter($allPertemuan, function ($p) use ($userMatkulIds) {
            return in_array($p['mata_kuliah_id'], $userMatkulIds);
        });
        $userPertemuanIds = array_column($userPertemuan, 'id');

        $tugasList = [];
        $now = new \DateTime();

        foreach ($allTugas as $t) {
            if (!in_array($t['pertemuan_id'], $userPertemuanIds)) continue;

            $pertemuan = null;
            foreach ($userPertemuan as $p) {
                if ($p['id'] == $t['pertemuan_id']) {
                    $pertemuan = $p;
                    break;
                }
            }

            $mk = null;
            foreach ($allMatkul as $m) {
                if ($m['id'] == $pertemuan['mata_kuliah_id']) {
                    $mk = $m;
                    break;
                }
            }

            $submission = null;
            foreach ($allPengumpulan as $pt) {
                if ($pt['tugas_id'] == $t['id']) {
                    $submission = $pt;
                    break;
                }
            }

            $deadline = new \DateTime($t['deadline']);
            $diff = $now->diff($deadline);
            $sisa_hari = ($now > $deadline) ? -$diff->days : $diff->days;
            $isLate = ($sisa_hari < 0 && !$submission);

            $t['nomor_pertemuan'] = $pertemuan['nomor_pertemuan'];
            $t['nama_mk'] = $mk['nama_mk'];
            $t['sisa_hari'] = $sisa_hari;
            $t['submission_id'] = $submission ? $submission['id'] : null;
            $t['submission_status'] = $submission ? $submission['status'] : null;
            $t['nilai'] = $submission['nilai'] ?? null;
            $t['submitted_at'] = $submission['submitted_at'] ?? null;

            $include = false;
            if ($filter == 'all') $include = true;
            if ($filter == 'pending' && !$submission && !$isLate) $include = true;
            if ($filter == 'submitted' && $submission) $include = true;
            if ($filter == 'late' && !$submission && $isLate) $include = true;

            if ($include) {
                $tugasList[] = $t;
            }
        }

        // Stats
        $stats = [
            'total' => 0,
            'pending' => 0,
            'submitted' => 0,
            'late' => 0
        ];

        // Recalculate full stats
        foreach ($allTugas as $t) {
            if (!in_array($t['pertemuan_id'], $userPertemuanIds)) continue;

            $submission = null;
            foreach ($allPengumpulan as $pt) {
                if ($pt['tugas_id'] == $t['id']) {
                    $submission = $pt;
                    break;
                }
            }

            $deadline = new \DateTime($t['deadline']);
            $diff = $now->diff($deadline);
            $isLate = ($now > $deadline);

            $stats['total']++;
            if ($submission) {
                $stats['submitted']++;
            } else {
                if ($isLate) {
                    $stats['late']++;
                } else {
                    $stats['pending']++;
                }
            }
        }

        // Sort
        usort($tugasList, function ($a, $b) {
            return strtotime($a['deadline']) - strtotime($b['deadline']);
        });

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

        $tugas = $this->db->findById('tugas', $id);

        if ($tugas) {
            $pertemuan = $this->db->findById('pertemuan', $tugas['pertemuan_id']);
            $mk = $this->db->findOne('mata_kuliah', ['id' => $pertemuan['mata_kuliah_id'], 'mahasiswa_id' => $user['id']]);

            if ($mk) {
                $sem = $this->db->findById('semester', $mk['semester_id']);

                $tugas['nomor_pertemuan'] = $pertemuan['nomor_pertemuan'];
                $tugas['mata_kuliah_id'] = $mk['id'];
                $tugas['nama_mk'] = $mk['nama_mk'];
                $tugas['semester_nama'] = $sem ? $sem['nama'] : '';

                $diff = (new \DateTime())->diff(new \DateTime($tugas['deadline']));
                $tugas['sisa_hari'] = ((new \DateTime()) > (new \DateTime($tugas['deadline']))) ? -$diff->days : $diff->days;
            } else {
                $tugas = null;
            }
        }

        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        // Get submission
        $submission = $this->db->findOne('pengumpulan_tugas', ['tugas_id' => $id, 'mahasiswa_id' => $user['id']]);

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
        $tugas = $this->db->findById('tugas', $tugasId);
        if ($tugas) {
            $pertemuan = $this->db->findById('pertemuan', $tugas['pertemuan_id']);
            if ($pertemuan) {
                $tugas['pertemuan_folder_id'] = $pertemuan['folder_gdrive_id'];
            }
        }

        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        // Check if already submitted
        $existing = $this->db->findOne('pengumpulan_tugas', ['tugas_id' => $tugasId, 'mahasiswa_id' => $user['id']]);

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

        // Upload to pertemuan folder or root
        $folderId = $tugas['pertemuan_folder_id'];
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

        flash('success', 'Tugas berhasil dikumpulkan!');
        redirect("tugas/detail?id=$tugasId");
    }

    /**
     * Buat tugas baru (untuk reminder pribadi)
     */
    /**
     * Buat tugas baru dengan file
     */
    public function create()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
                return;
            }
            redirect('tugas');
        }

        $pertemuanId = $_POST['pertemuan_id'] ?? null;
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');

        if (!$pertemuanId || !$judul || !isset($_FILES['file'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
                return;
            }
            flash('error', 'Data tidak lengkap');
            redirect("pertemuan?id=$pertemuanId");
        }

        // Get pertemuan to find folder
        $pertemuan = $this->db->findById('pertemuan', $pertemuanId);
        if (!$pertemuan) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => 'Pertemuan tidak ditemukan']);
                return;
            }
            flash('error', 'Pertemuan tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Upload to Drive
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);

        if (!$tokens) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => 'Token invalid']);
                return;
            }
            redirect('login');
        }

        $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);

        // Use pertemuan folder or root
        $folderId = $pertemuan['folder_gdrive_id'] ?? null;

        $uploadResult = $driveHelper->uploadFromPost('file', $folderId);

        if (!$uploadResult['success']) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                return;
            }
            flash('error', 'Gagal upload: ' . $uploadResult['error']);
            redirect("pertemuan?id=$pertemuanId");
        }

        $file = $_FILES['file'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        $this->db->insert('tugas', [
            'pertemuan_id' => $pertemuanId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'nama_file' => $file['name'],
            'tipe_file' => $extension,
            'ukuran_file' => $file['size'],
            'file_gdrive_id' => $uploadResult['id'],
            'file_gdrive_url' => $uploadResult['url'],
            'created_by' => $user['id'],
            'deadline' => date('Y-m-d H:i:s') // Dummy deadline for compatibility
        ]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            return;
        }

        flash('success', 'Tugas berhasil ditambahkan');
        redirect("pertemuan?id=$pertemuanId");
    }

    /**
     * Hapus tugas
     */
    public function delete()
    {
        $user = auth();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            flash('error', 'ID tidak valid');
            redirect('tugas');
        }

        $tugas = $this->db->findById('tugas', $id);

        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        // Delete from Drive
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);
        if ($tokens && isset($tugas['file_gdrive_id'])) {
            $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);
            $driveHelper->deleteFile($tugas['file_gdrive_id']);
        }

        $this->db->delete('tugas', $id);

        flash('success', 'Tugas berhasil dihapus');
        redirect("pertemuan?id=" . $tugas['pertemuan_id']);
    }
}
