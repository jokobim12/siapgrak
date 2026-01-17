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
        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]); // Add this
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
            // Filter out files/archives (only show Todo items)
            if (!empty($t['tipe_file'])) continue;

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

            if (!empty($t['deadline'])) {
                $deadline = new \DateTime($t['deadline']);
                $diff = $now->diff($deadline);
                $sisa_hari = ($now > $deadline) ? -$diff->days : $diff->days;
                $isLate = ($sisa_hari < 0 && !$submission);
            } else {
                $sisa_hari = 0;
                $isLate = false;
            }

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
            if (!empty($t['tipe_file'])) continue;
            if (!in_array($t['pertemuan_id'], $userPertemuanIds)) continue;

            $submission = null;
            foreach ($allPengumpulan as $pt) {
                if ($pt['tugas_id'] == $t['id']) {
                    $submission = $pt;
                    break;
                }
            }

            $isLate = false;
            if (!empty($t['deadline'])) {
                $deadline = new \DateTime($t['deadline']);
                $isLate = ($now > $deadline);
            }

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
            $da = !empty($a['deadline']) ? strtotime($a['deadline']) : 0;
            $db = !empty($b['deadline']) ? strtotime($b['deadline']) : 0;
            return $da - $db;
        });

        view('tugas.index', [
            'tugasList' => $tugasList,
            'stats' => $stats,
            'filter' => $filter,
            'allMatkul' => $allMatkul,
            'semesters' => $semesters
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

                if (!empty($tugas['deadline'])) {
                    $diff = (new \DateTime())->diff(new \DateTime($tugas['deadline']));
                    $tugas['sisa_hari'] = ((new \DateTime()) > (new \DateTime($tugas['deadline']))) ? -$diff->days : $diff->days;
                } else {
                    $tugas['sisa_hari'] = 0;
                }
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

        if (!$tugasId) {
            flash('error', 'Tugas tidak valid');
            redirect('tugas');
        }

        // Get tugas
        $tugas = $this->db->findById('tugas', $tugasId);
        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        // Check if already done
        $existing = $this->db->findOne('pengumpulan_tugas', ['tugas_id' => $tugasId, 'mahasiswa_id' => $user['id']]);

        if ($existing) {
            // Already done
            redirect('tugas');
        }

        // Determine status
        $status = 'submitted';
        if (strtotime($tugas['deadline']) < time()) {
            $status = 'late';
        }

        // Save submission (Mark as Done)
        $this->db->insert('pengumpulan_tugas', [
            'tugas_id' => $tugasId,
            'mahasiswa_id' => $user['id'],
            'nama_file' => null,
            'file_gdrive_id' => null,
            'file_gdrive_url' => null,
            'catatan' => 'Selesai',
            'status' => $status,
            'submitted_at' => date('Y-m-d H:i:s')
        ]);

        redirect('tugas');
    }

    public function uncheck()
    {
        $user = auth();
        $tugasId = $_POST['tugas_id'] ?? null;

        if ($tugasId) {
            // Cari submission
            $submission = $this->db->findOne('pengumpulan_tugas', ['tugas_id' => $tugasId, 'mahasiswa_id' => $user['id']]);

            if ($submission) {
                // Delete submission (Mark as Undone)
                $this->db->delete('pengumpulan_tugas', $submission['id']);
            }
        }

        redirect('tugas');
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
            redirect('tugas');
        }

        $pertemuanId = $_POST['pertemuan_id'] ?? null;
        $mataKuliahId = $_POST['mata_kuliah_id'] ?? null; // New Input
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = $_POST['deadline'] ?? null;
        $redirectUrl = $_POST['redirect_url'] ?? "pertemuan?id=$pertemuanId"; // Default redirect

        // Logic Auto-Pertemuan jika input dari Menu Tugas
        if (!$pertemuanId && $mataKuliahId) {
            // Cari pertemuan terakhir dari MK ini
            $allPertemuan = $this->db->find('pertemuan', ['mata_kuliah_id' => $mataKuliahId]);

            if (!empty($allPertemuan)) {
                // Ambil yang terakhir (anggap array terurut atau sort dulu)
                usort($allPertemuan, function ($a, $b) {
                    return $b['nomor_pertemuan'] - $a['nomor_pertemuan'];
                });
                $pertemuanId = $allPertemuan[0]['id'];
            } else {
                // Jika belum ada pertemuan, harus buat satu?
                // Kita skip dulu buat otomatis yang kompleks, return error saja suruh buat pertemuan dulu
                flash('error', 'Mata Kuliah ini belum memiliki pertemuan. Silakan buat pertemuan pertama dulu di menu Mata Kuliah.');
                redirect('tugas');
            }
            $redirectUrl = 'tugas'; // Redirect balik ke menu tugas
        }

        if (!$pertemuanId || !$judul || !$deadline) {
            flash('error', 'Data tidak lengkap. Judul, Mata Kuliah/Pertemuan, dan Deadline wajib diisi.');
            redirect('tugas');
        }

        $this->db->insert('tugas', [
            'pertemuan_id' => $pertemuanId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'created_by' => $user['id'],
            'deadline' => $deadline,
            'nama_file' => null,
            'tipe_file' => null,
            'ukuran_file' => 0,
            'file_gdrive_id' => null,
            'file_gdrive_url' => null
        ]);

        flash('success', 'Tugas / Todo berhasil ditambahkan!');
        redirect($redirectUrl);
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
            $res = $driveHelper->deleteFile($tugas['file_gdrive_id']);

            if (!$res['success']) {
                if (strpos($res['error'], 'not initialized') !== false) {
                    // Biarkan user tau token habis, tapi tetap hapus dari DB agar tidak nyangkut
                    flash('warning', 'Tugas dihapus dari sistem, namun gagal dihapus dari Google Drive (Sesi habis). Silakan Logout & Login.');
                }
            }
        }

        $this->db->delete('tugas', $id);

        flash('success', 'Tugas berhasil dihapus');

        // Logic redirect: Jika ini tugas file (Materi/Arsip), balik ke pertemuan.
        // Jika ini Todo (tipe_file null), balik ke menu Tugas.
        if (!empty($tugas['tipe_file'])) {
            redirect("pertemuan?id=" . $tugas['pertemuan_id']);
        } else {
            redirect("tugas");
        }
    }
    public function update()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tugas');
        }

        $id = $_POST['id'] ?? null;
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $deadline = $_POST['deadline'] ?? null;

        if (!$id || !$judul || !$deadline) {
            flash('error', 'Data tidak lengkap');
            redirect("tugas/detail?id=$id");
        }

        $tugas = $this->db->findById('tugas', $id);
        if (!$tugas) {
            flash('error', 'Tugas tidak ditemukan');
            redirect('tugas');
        }

        $this->db->update('tugas', $id, [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'deadline' => $deadline
        ]);

        flash('success', 'Tugas berhasil diperbarui');
        redirect("tugas/detail?id=$id");
    }
    /**
     * Upload File Tugas (Arsip/Submission)
     * Diakses dari Pertemuan > Detail
     */
    public function upload()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 0); // Suppress HTML errors for AJAX
        header('Content-Type: application/json');

        try {
            $user = auth();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $pertemuanId = $_POST['pertemuan_id'] ?? null;
            $judul = sanitize($_POST['judul'] ?? '');
            $deskripsi = sanitize($_POST['deskripsi'] ?? '');

            if (!$pertemuanId || empty($_FILES['file']['name'])) {
                throw new \Exception('Data tidak lengkap. Pertemuan ID dan File wajib diisi.');
            }

            $pertemuan = $this->db->findById('pertemuan', $pertemuanId);
            if (!$pertemuan) {
                throw new \Exception('Pertemuan tidak ditemukan');
            }

            // Google Drive Logic
            $authController = new AuthController();
            $tokens = $authController->getTokens($user['id']);

            if (!$tokens) {
                throw new \Exception('Token autentikasi Google tidak valid/kadaluarsa. Login ulang.');
            }

            $driveHelper = new GoogleDriveHelper($tokens['access_token'], $tokens['refresh_token']);

            // 1. Dapatkan/Buat struktur folder dasar (sama dengan Materi)
            $pertemuanFolderId = $pertemuan['folder_gdrive_id'];

            if (!$pertemuanFolderId) {
                // ... Rebuild logic if missing (Copy from PertemuanController logic basically)
                // Simplify: Assume PertemuanController handles folder creation usually, 
                // but for robustness we re-implement quick check or fail.
                // Let's implement full rebuild for safety.

                $mkInfo = $this->db->findById('mata_kuliah', $pertemuan['mata_kuliah_id']);
                if (!$mkInfo) throw new \Exception('MK tidak ditemukan');

                $sem = $this->db->findById('semester', $mkInfo['semester_id']);
                $semesterName = $sem ? $sem['nama'] : 'Semester ???';

                // Root -> Semester
                $semFolder = $driveHelper->findOrCreateFolder($semesterName, null);
                if (!$semFolder['success']) {
                    if (strpos($semFolder['error'], 'not initialized') !== false) {
                        throw new \Exception('Sesi Google berakhir. Silakan Logout dan Login kembali.');
                    }
                    throw new \Exception('Gagal folder Semester: ' . $semFolder['error']);
                }

                // Semester -> MK
                $mkFolder = $driveHelper->findOrCreateFolder($mkInfo['nama_mk'], $semFolder['id']);
                if (!$mkFolder['success']) {
                    if (strpos($mkFolder['error'], 'not initialized') !== false) {
                        throw new \Exception('Sesi Google berakhir. Silakan Logout dan Login kembali.');
                    }
                    throw new \Exception('Gagal folder MK: ' . $mkFolder['error']);
                }

                // MK -> Pertemuan
                $pFolder = $driveHelper->findOrCreateFolder("Pertemuan " . $pertemuan['nomor_pertemuan'], $mkFolder['id']);
                if (!$pFolder['success']) {
                    if (strpos($pFolder['error'], 'not initialized') !== false) {
                        throw new \Exception('Sesi Google berakhir. Silakan Logout dan Login kembali.');
                    }
                    throw new \Exception('Gagal folder Pertemuan: ' . $pFolder['error']);
                }

                $pertemuanFolderId = $pFolder['id'];
                $this->db->update('pertemuan', $pertemuanId, ['folder_gdrive_id' => $pertemuanFolderId]);
            }

            // 2. Buat subfolder "Tugas" di dalam folder Pertemuan
            $tugasFolder = $driveHelper->findOrCreateFolder('Tugas', $pertemuanFolderId);
            if (!$tugasFolder['success']) {
                if (strpos($tugasFolder['error'], 'not initialized') !== false) {
                    throw new \Exception('Sesi Google berakhir. Silakan Logout dan Login kembali.');
                }
                throw new \Exception('Gagal membuat folder Tugas: ' . $tugasFolder['error']);
            }

            // 3. Upload File ke folder Tugas
            $uploadResult = $driveHelper->uploadFromPost('file', $tugasFolder['id']);
            if (!$uploadResult['success']) {
                if (strpos($uploadResult['error'], 'not initialized') !== false) {
                    throw new \Exception('Sesi Google berakhir. Silakan Logout dan Login kembali.');
                }
                throw new \Exception('Gagal upload ke Drive: ' . $uploadResult['error']);
            }

            // 4. Simpan ke Database
            $file = $_FILES['file'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

            $this->db->insert('tugas', [
                'pertemuan_id' => $pertemuanId,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'created_by' => $user['id'],
                'deadline' => null, // Arsip tugas biasanya tidak butuh deadline sistem todo
                'nama_file' => $file['name'],
                'tipe_file' => $extension,
                'ukuran_file' => $file['size'],
                'file_gdrive_id' => $uploadResult['id'],
                'file_gdrive_url' => $uploadResult['url']
            ]);

            echo json_encode(['success' => true, 'message' => 'File tugas berhasil diupload']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
