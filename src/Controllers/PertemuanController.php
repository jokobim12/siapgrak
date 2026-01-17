<?php

/**
 * Pertemuan Controller
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/Helpers/GoogleDriveHelper.php';
require_once ROOT_PATH . '/src/Controllers/AuthController.php';

use App\Helpers\GoogleDriveHelper;

class PertemuanController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Detail pertemuan dengan materi dan tugas
     */
    public function detail()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('mata-kuliah');
        }

        // Get pertemuan
        $pertemuan = $this->db->fetch(
            "SELECT p.*, mk.nama_mk, mk.kode_mk, mk.dosen, mk.kelas_id, mk.folder_gdrive_id as mk_folder_id,
                    k.nama_kelas, s.nama as semester_nama
             FROM pertemuan p
             JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN semester s ON s.id = k.semester_id
             WHERE p.id = ?",
            [$id]
        );

        if (!$pertemuan) {
            redirect('mata-kuliah');
        }

        // Check akses
        $hasAccess = $this->db->fetch(
            "SELECT 1 FROM kelas_mahasiswa WHERE kelas_id = ? AND mahasiswa_id = ?",
            [$pertemuan['kelas_id'], $user['id']]
        );

        if (!$hasAccess) {
            flash('error', 'Anda tidak memiliki akses');
            redirect('mata-kuliah');
        }

        // Get materi
        $materiList = $this->db->fetchAll(
            "SELECT m.*, mhs.nama as uploader_nama
             FROM materi m
             JOIN mahasiswa mhs ON mhs.id = m.uploaded_by
             WHERE m.pertemuan_id = ?
             ORDER BY m.created_at DESC",
            [$id]
        );

        // Get tugas
        $tugasList = $this->db->fetchAll(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = t.id) as total_submit,
                    (SELECT 1 FROM pengumpulan_tugas WHERE tugas_id = t.id AND mahasiswa_id = ?) as sudah_submit,
                    DATEDIFF(t.deadline, NOW()) as sisa_hari
             FROM tugas t
             WHERE t.pertemuan_id = ?
             ORDER BY t.deadline ASC",
            [$user['id'], $id]
        );

        // Update progress
        $this->db->query(
            "UPDATE progress_mahasiswa 
             SET pertemuan_selesai = pertemuan_selesai + 1, last_accessed_at = NOW()
             WHERE mahasiswa_id = ? AND mata_kuliah_id = ? AND pertemuan_selesai < 18",
            [$user['id'], $pertemuan['mata_kuliah_id']]
        );

        view('pertemuan.detail', [
            'pertemuan' => $pertemuan,
            'materiList' => $materiList,
            'tugasList' => $tugasList
        ]);
    }

    /**
     * Upload materi ke pertemuan
     */
    public function uploadMateri()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('mata-kuliah');
        }

        $pertemuanId = $_POST['pertemuan_id'] ?? null;
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');

        if (!$pertemuanId || !$judul || !isset($_FILES['file'])) {
            flash('error', 'Data tidak lengkap');
            redirect("pertemuan?id=$pertemuanId");
        }

        // Get pertemuan dan folder
        $pertemuan = $this->db->fetch(
            "SELECT p.*, mk.nama_mk, mk.folder_gdrive_id as mk_folder_id
             FROM pertemuan p
             JOIN mata_kuliah mk ON mk.id = p.mata_kuliah_id
             WHERE p.id = ?",
            [$pertemuanId]
        );

        if (!$pertemuan) {
            flash('error', 'Pertemuan tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Get user's Google tokens
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);

        if (!$tokens) {
            flash('error', 'Token autentikasi tidak valid. Silakan login ulang.');
            redirect('login');
        }

        $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);

        // Get or create folder structure
        $mahasiswa = $this->db->fetch("SELECT * FROM mahasiswa WHERE id = ?", [$user['id']]);
        $rootFolderId = $mahasiswa['gdrive_folder_id'];

        // Ensure pertemuan folder exists
        $pertemuanFolderId = $pertemuan['folder_gdrive_id'];

        if (!$pertemuanFolderId) {
            // Create folder structure if not exists
            $folderResult = $driveHelper->createFolder(
                "P{$pertemuan['nomor_pertemuan']} - {$pertemuan['judul']}",
                $rootFolderId
            );

            if ($folderResult['success']) {
                $pertemuanFolderId = $folderResult['id'];
                $this->db->update(
                    'pertemuan',
                    ['folder_gdrive_id' => $pertemuanFolderId],
                    'id = ?',
                    [$pertemuanId]
                );
            } else {
                flash('error', 'Gagal membuat folder: ' . $folderResult['error']);
                redirect("pertemuan?id=$pertemuanId");
            }
        }

        // Upload file
        $uploadResult = $driveHelper->uploadFromPost('file', $pertemuanFolderId);

        if (!$uploadResult['success']) {
            flash('error', 'Gagal upload file: ' . $uploadResult['error']);
            redirect("pertemuan?id=$pertemuanId");
        }

        // Save to database
        $file = $_FILES['file'];
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        $this->db->insert('materi', [
            'pertemuan_id' => $pertemuanId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'nama_file' => $file['name'],
            'tipe_file' => $extension,
            'ukuran_file' => $file['size'],
            'file_gdrive_id' => $uploadResult['id'],
            'file_gdrive_url' => $uploadResult['url'],
            'uploaded_by' => $user['id']
        ]);

        // Update progress
        $this->db->query(
            "UPDATE progress_mahasiswa 
             SET materi_selesai = materi_selesai + 1
             WHERE mahasiswa_id = ? AND mata_kuliah_id = ?",
            [$user['id'], $pertemuan['mata_kuliah_id']]
        );

        // Refresh token if needed
        if ($driveHelper->isTokenExpired()) {
            $newToken = $driveHelper->getAccessToken();
            $this->db->update(
                'mahasiswa',
                [
                    'access_token' => json_encode($newToken),
                    'token_expires_at' => date('Y-m-d H:i:s', time() + 3600)
                ],
                'id = ?',
                [$user['id']]
            );
        }

        flash('success', 'Materi berhasil diupload');
        redirect("pertemuan?id=$pertemuanId");
    }

    /**
     * Delete materi
     */
    public function deleteMateri()
    {
        $user = auth();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID tidak valid']);
            return;
        }

        $materi = $this->db->fetch(
            "SELECT * FROM materi WHERE id = ? AND uploaded_by = ?",
            [$id, $user['id']]
        );

        if (!$materi) {
            echo json_encode(['success' => false, 'error' => 'Materi tidak ditemukan']);
            return;
        }

        // Delete from Google Drive
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);

        if ($tokens) {
            $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);
            $driveHelper->deleteFile($materi['file_gdrive_id']);
        }

        // Delete from database
        $this->db->delete('materi', 'id = ?', [$id]);

        echo json_encode(['success' => true]);
    }
}
