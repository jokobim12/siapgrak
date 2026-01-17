<?php

/**
 * Mata Kuliah Controller
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/Helpers/GoogleDriveHelper.php';

use App\Helpers\GoogleDriveHelper;

class MataKuliahController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Daftar mata kuliah per kelas
     */
    public function index()
    {
        $user = auth();
        $kelasId = $_GET['kelas_id'] ?? null;
        $semesterId = $_GET['semester_id'] ?? null;

        // Get semester list
        $semesters = $this->db->fetchAll("SELECT * FROM semester ORDER BY tanggal_mulai DESC");

        // Get kelas mahasiswa
        $kelasQuery = "SELECT k.*, s.nama as semester_nama, s.id as semester_id
                       FROM kelas k
                       JOIN semester s ON s.id = k.semester_id
                       JOIN kelas_mahasiswa km ON km.kelas_id = k.id
                       WHERE km.mahasiswa_id = ?";
        $params = [$user['id']];

        if ($semesterId) {
            $kelasQuery .= " AND k.semester_id = ?";
            $params[] = $semesterId;
        }

        $kelasList = $this->db->fetchAll($kelasQuery, $params);

        // Get mata kuliah
        $mataKuliahList = [];
        if ($kelasId) {
            $mataKuliahList = $this->db->fetchAll(
                "SELECT mk.*, k.nama_kelas,
                        (SELECT COUNT(*) FROM pertemuan WHERE mata_kuliah_id = mk.id) as total_pertemuan,
                        (SELECT COUNT(*) FROM materi m JOIN pertemuan p ON p.id = m.pertemuan_id WHERE p.mata_kuliah_id = mk.id) as total_materi,
                        (SELECT COUNT(*) FROM tugas t JOIN pertemuan p ON p.id = t.pertemuan_id WHERE p.mata_kuliah_id = mk.id) as total_tugas
                 FROM mata_kuliah mk
                 JOIN kelas k ON k.id = mk.kelas_id
                 WHERE mk.kelas_id = ?",
                [$kelasId]
            );
        }

        view('mata_kuliah.index', [
            'semesters' => $semesters,
            'kelasList' => $kelasList,
            'mataKuliahList' => $mataKuliahList,
            'selectedKelasId' => $kelasId,
            'selectedSemesterId' => $semesterId
        ]);
    }

    /**
     * Detail mata kuliah dengan pertemuan P1-P18
     */
    public function detail()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('mata-kuliah');
        }

        // Get mata kuliah
        $mataKuliah = $this->db->fetch(
            "SELECT mk.*, k.nama_kelas, s.nama as semester_nama
             FROM mata_kuliah mk
             JOIN kelas k ON k.id = mk.kelas_id
             JOIN semester s ON s.id = k.semester_id
             WHERE mk.id = ?",
            [$id]
        );

        if (!$mataKuliah) {
            redirect('mata-kuliah');
        }

        // Check akses mahasiswa
        $hasAccess = $this->db->fetch(
            "SELECT 1 FROM kelas_mahasiswa WHERE kelas_id = ? AND mahasiswa_id = ?",
            [$mataKuliah['kelas_id'], $user['id']]
        );

        if (!$hasAccess) {
            flash('error', 'Anda tidak memiliki akses ke mata kuliah ini');
            redirect('mata-kuliah');
        }

        // Get pertemuan P1-P18
        $pertemuanList = $this->db->fetchAll(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM materi WHERE pertemuan_id = p.id) as total_materi,
                    (SELECT COUNT(*) FROM tugas WHERE pertemuan_id = p.id) as total_tugas
             FROM pertemuan p
             WHERE p.mata_kuliah_id = ?
             ORDER BY p.nomor_pertemuan ASC",
            [$id]
        );

        // Jika pertemuan belum ada, buat P1-P18
        if (empty($pertemuanList)) {
            for ($i = 1; $i <= 18; $i++) {
                $this->db->insert('pertemuan', [
                    'mata_kuliah_id' => $id,
                    'nomor_pertemuan' => $i,
                    'judul' => "Pertemuan $i"
                ]);
            }

            $pertemuanList = $this->db->fetchAll(
                "SELECT p.*,
                        (SELECT COUNT(*) FROM materi WHERE pertemuan_id = p.id) as total_materi,
                        (SELECT COUNT(*) FROM tugas WHERE pertemuan_id = p.id) as total_tugas
                 FROM pertemuan p
                 WHERE p.mata_kuliah_id = ?
                 ORDER BY p.nomor_pertemuan ASC",
                [$id]
            );
        }

        // Update/create progress
        $progress = $this->db->fetch(
            "SELECT * FROM progress_mahasiswa WHERE mahasiswa_id = ? AND mata_kuliah_id = ?",
            [$user['id'], $id]
        );

        if (!$progress) {
            $this->db->insert('progress_mahasiswa', [
                'mahasiswa_id' => $user['id'],
                'mata_kuliah_id' => $id,
                'last_accessed_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->db->update(
                'progress_mahasiswa',
                ['last_accessed_at' => date('Y-m-d H:i:s')],
                'mahasiswa_id = ? AND mata_kuliah_id = ?',
                [$user['id'], $id]
            );
        }

        view('mata_kuliah.detail', [
            'mataKuliah' => $mataKuliah,
            'pertemuanList' => $pertemuanList
        ]);
    }
}
