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

        // Get pertemuan with ownership check
        $pertemuan = $this->db->findById('pertemuan', $id);
        if ($pertemuan) {
            $mk = $this->db->findOne('mata_kuliah', ['id' => $pertemuan['mata_kuliah_id'], 'mahasiswa_id' => $user['id']]);
            if ($mk) {
                $pertemuan['nama_mk'] = $mk['nama_mk'];
                $pertemuan['mk_folder_id'] = $mk['folder_gdrive_id'];

                $semester = $this->db->findById('semester', $mk['semester_id']);
                $pertemuan['semester_nama'] = $semester ? $semester['nama'] : '';
            } else {
                $pertemuan = null; // Not owned by user
            }
        }

        if (!$pertemuan) {
            flash('error', 'Pertemuan tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Get materi
        $allMateri = $this->db->find('materi', ['pertemuan_id' => $id]);

        // Enrich materi with uploader name
        foreach ($allMateri as &$m) {
            $uploader = $this->db->findById('mahasiswa', $m['uploaded_by']);
            $m['uploader_nama'] = $uploader ? $uploader['nama'] : 'Unknown';
        }

        // Sort materi descending
        usort($allMateri, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $materiList = $allMateri;

        // Get tugas
        $allTugas = $this->db->find('tugas', ['pertemuan_id' => $id]);
        $pengumpulan = $this->db->all('pengumpulan_tugas');

        foreach ($allTugas as &$t) {
            $submitted = array_filter($pengumpulan, function ($p) use ($t, $user) {
                return $p['tugas_id'] == $t['id'];
            });
            $t['total_submit'] = count($submitted);

            $userSubmitted = array_filter($submitted, function ($p) use ($user) {
                return $p['mahasiswa_id'] == $user['id'];
            });
            $t['sudah_submit'] = !empty($userSubmitted);

            $now = new \DateTime();
            if (!empty($t['deadline'])) {
                $deadline = new \DateTime($t['deadline']);
                $diff = $now->diff($deadline);
                $t['sisa_hari'] = ($now > $deadline) ? -$diff->days : $diff->days;
            } else {
                $t['sisa_hari'] = null;
            }
        }

        // Sort tugas by deadline asc
        usort($allTugas, function ($a, $b) {
            $timeA = !empty($a['deadline']) ? strtotime($a['deadline']) : 0;
            $timeB = !empty($b['deadline']) ? strtotime($b['deadline']) : 0;
            return $timeA - $timeB;
        });

        $tugasList = $allTugas;

        // Update progress
        $progress = $this->db->findOne('progress_mahasiswa', [
            'mahasiswa_id' => $user['id'],
            'mata_kuliah_id' => $pertemuan['mata_kuliah_id']
        ]);

        if ($progress && $progress['pertemuan_selesai'] < 18) {
            $this->db->update('progress_mahasiswa', $progress['id'], [
                'pertemuan_selesai' => $progress['pertemuan_selesai'] + 1,
                'last_accessed_at' => date('Y-m-d H:i:s')
            ]);
        }

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
        $pertemuan = $this->db->findById('pertemuan', $pertemuanId);

        if ($pertemuan) {
            $mk = $this->db->findById('mata_kuliah', $pertemuan['mata_kuliah_id']);
            if ($mk) {
                $pertemuan['nama_mk'] = $mk['nama_mk'];
                $pertemuan['mk_folder_id'] = $mk['folder_gdrive_id'];
            }
        }

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

        // Get mahasiswa dan info lengkap untuk struktur folder
        $mahasiswa = $this->db->findById('mahasiswa', $user['id']);

        // Get info lengkap untuk nama folder
        $mkInfo = $this->db->findById('mata_kuliah', $pertemuan['mata_kuliah_id']);
        if ($mkInfo) {
            $sem = $this->db->findById('semester', $mkInfo['semester_id']);
            $mkInfo['semester_nama'] = $sem ? $sem['nama'] : '';
        }

        // Dapatkan atau buat folder pertemuan dengan struktur:
        // Semester → Mata Kuliah → Pertemuan (langsung di root Drive)
        $pertemuanFolderId = $pertemuan['folder_gdrive_id'];

        if (!$pertemuanFolderId) {
            // 1. Cek/buat folder Semester (langsung di root Drive, tanpa parent)
            $semesterFolderName = $mkInfo['semester_nama'];
            $semesterFolder = $driveHelper->findOrCreateFolder($semesterFolderName, null);
            if (!$semesterFolder['success']) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => 'Gagal membuat folder semester: ' . $semesterFolder['error']]);
                    return;
                }
                flash('error', 'Gagal membuat folder semester: ' . $semesterFolder['error']);
                redirect("pertemuan?id=$pertemuanId");
            }

            // 2. Cek/buat folder Mata Kuliah (nama saja, tanpa kode)
            $mkFolderName = $mkInfo['nama_mk'];
            $mkFolder = $driveHelper->findOrCreateFolder($mkFolderName, $semesterFolder['id']);
            if (!$mkFolder['success']) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => 'Gagal membuat folder mata kuliah: ' . $mkFolder['error']]);
                    return;
                }
                flash('error', 'Gagal membuat folder mata kuliah: ' . $mkFolder['error']);
                redirect("pertemuan?id=$pertemuanId");
            }

            // Update folder_gdrive_id di mata_kuliah
            $this->db->update('mata_kuliah', $pertemuan['mata_kuliah_id'], ['folder_gdrive_id' => $mkFolder['id']]);

            // 3. Cek/buat folder Pertemuan
            $pertemuanFolderName = "Pertemuan " . $pertemuan['nomor_pertemuan'];
            $pFolder = $driveHelper->findOrCreateFolder($pertemuanFolderName, $mkFolder['id']);
            if (!$pFolder['success']) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => false, 'error' => 'Gagal membuat folder pertemuan: ' . $pFolder['error']]);
                    return;
                }
                flash('error', 'Gagal membuat folder pertemuan: ' . $pFolder['error']);
                redirect("pertemuan?id=$pertemuanId");
            }

            $pertemuanFolderId = $pFolder['id'];
            $this->db->update('pertemuan', $pertemuanId, ['folder_gdrive_id' => $pertemuanFolderId]);
        }

        // Upload file
        $uploadResult = $driveHelper->uploadFromPost('file', $pertemuanFolderId);

        if (!$uploadResult['success']) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => false, 'error' => $uploadResult['error']]);
                return;
            }
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
        $progress = $this->db->findOne('progress_mahasiswa', [
            'mahasiswa_id' => $user['id'],
            'mata_kuliah_id' => $pertemuan['mata_kuliah_id']
        ]);
        if ($progress) {
            $this->db->update('progress_mahasiswa', $progress['id'], [
                'materi_selesai' => $progress['materi_selesai'] + 1
            ]);
        }

        // Refresh token if needed
        if ($driveHelper->isTokenExpired()) {
            $newToken = $driveHelper->getAccessToken();
            $newToken = $driveHelper->getAccessToken();
            $this->db->update(
                'mahasiswa',
                $user['id'],
                [
                    'access_token' => json_encode($newToken),
                    'token_expires_at' => date('Y-m-d H:i:s', time() + 3600)
                ]
            );
        }

        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            return;
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
            flash('error', 'ID tidak valid');
            redirect('mata-kuliah');
        }

        $materi = $this->db->findOne('materi', ['id' => $id, 'uploaded_by' => $user['id']]);

        if (!$materi) {
            flash('error', 'Materi tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Delete from Google Drive
        $authController = new AuthController();
        $tokens = $authController->getTokens($user['id']);

        if ($tokens) {
            $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);
            $driveHelper->deleteFile($materi['file_gdrive_id']);
        }

        // Delete from database
        $this->db->delete('materi', $id);

        flash('success', 'Materi berhasil dihapus');
        redirect("pertemuan?id=" . $materi['pertemuan_id']);
    }
}
