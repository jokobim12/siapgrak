<?php

/**
 * Mata Kuliah Controller - Self Manage by Mahasiswa
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class MataKuliahController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Daftar mata kuliah
     */
    public function index()
    {
        $user = auth();
        $semesterId = $_GET['semester_id'] ?? null;

        // Get semesters for filter
        // Get semesters for filter
        $allSemesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);

        // Sort semesters manually
        usort($allSemesters, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Get mata kuliah
        $conditions = ['mahasiswa_id' => $user['id']];
        if ($semesterId) {
            $conditions['semester_id'] = $semesterId;
        }

        $mataKuliahList = $this->db->find('mata_kuliah', $conditions);

        // Prep data needed for joins
        $pertemuan = $this->db->all('pertemuan');
        $materi = $this->db->all('materi');

        foreach ($mataKuliahList as &$mk) {
            // Join semester
            $sem = $this->db->findById('semester', $mk['semester_id']);
            $mk['semester_nama'] = $sem ? $sem['nama'] : '-';

            // Count materi
            $mkPertemuan = array_filter($pertemuan, function ($p) use ($mk) {
                return $p['mata_kuliah_id'] == $mk['id'];
            });
            $mkPertemuanIds = array_column($mkPertemuan, 'id');

            $mkMateri = array_filter($materi, function ($m) use ($mkPertemuanIds) {
                return in_array($m['pertemuan_id'], $mkPertemuanIds);
            });
            $mk['total_materi'] = count($mkMateri);
        }

        // Sort MK by latest
        usort($mataKuliahList, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        view('mata_kuliah.index', [
            'mataKuliahList' => $mataKuliahList,
            'semesters' => $allSemesters,
            'selectedSemester' => $semesterId
        ]);
    }

    /**
     * Form tambah mata kuliah
     */
    public function tambah()
    {
        $user = auth();

        // Get semesters
        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);
        usort($semesters, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        if (empty($semesters)) {
            flash('error', 'Tambahkan semester terlebih dahulu');
            redirect('semester/tambah');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = sanitize($_POST['nama'] ?? '');
            $semesterId = (int)($_POST['semester_id'] ?? 0);

            if (empty($nama) || !$semesterId) {
                flash('error', 'Nama dan semester harus diisi');
                redirect('mata-kuliah/tambah');
            }

            // Insert mata kuliah
            $mkId = $this->db->insert('mata_kuliah', [
                'nama_mk' => $nama,
                'semester_id' => $semesterId,
                'mahasiswa_id' => $user['id']
            ]);

            // Init progress mahasiswa
            $this->db->insert('progress_mahasiswa', [
                'mahasiswa_id' => $user['id'],
                'mata_kuliah_id' => $mkId,
                'pertemuan_selesai' => 0,
                'materi_selesai' => 0
            ]);

            // Create 18 pertemuan otomatis
            for ($i = 1; $i <= 18; $i++) {
                $this->db->insert('pertemuan', [
                    'mata_kuliah_id' => $mkId,
                    'nomor_pertemuan' => $i,
                    'judul' => "Pertemuan $i"
                ]);
            }

            flash('success', 'Mata kuliah berhasil ditambahkan dengan 18 pertemuan');
            redirect('mata-kuliah/detail?id=' . $mkId);
        }

        view('mata_kuliah.tambah', [
            'semesters' => $semesters
        ]);
    }

    /**
     * Detail mata kuliah dengan pertemuan
     */
    public function detail()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('mata-kuliah');
        }

        $mataKuliah = $this->db->findOne('mata_kuliah', ['id' => $id, 'mahasiswa_id' => $user['id']]);
        if ($mataKuliah) {
            $sem = $this->db->findById('semester', $mataKuliah['semester_id']);
            $mataKuliah['semester_nama'] = $sem ? $sem['nama'] : '';
        }

        if (!$mataKuliah) {
            flash('error', 'Mata kuliah tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Get pertemuan dengan jumlah materi
        // Get pertemuan
        $allPertemuan = $this->db->find('pertemuan', ['mata_kuliah_id' => $id]);

        $materiList = $this->db->all('materi');
        $tugasList = $this->db->all('tugas');

        $pertemuanList = [];
        foreach ($allPertemuan as $p) {
            $materiCount = count(array_filter($materiList, function ($m) use ($p) {
                return $m['pertemuan_id'] == $p['id'];
            }));

            $tugasCount = count(array_filter($tugasList, function ($t) use ($p) {
                return $t['pertemuan_id'] == $p['id'];
            }));

            $p['total_materi'] = $materiCount;
            $p['total_tugas'] = $tugasCount;
            $pertemuanList[] = $p;
        }

        // Sort pertemuan
        usort($pertemuanList, function ($a, $b) {
            return $a['nomor_pertemuan'] - $b['nomor_pertemuan'];
        });

        view('mata_kuliah.detail', [
            'mataKuliah' => $mataKuliah,
            'pertemuanList' => $pertemuanList
        ]);
    }

    /**
     * Hapus mata kuliah
     */
    public function hapus()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('mata-kuliah');
        }

        // Check ownership
        $mk = $this->db->findOne('mata_kuliah', ['id' => $id, 'mahasiswa_id' => $user['id']]);

        if (!$mk) {
            flash('error', 'Mata kuliah tidak ditemukan');
            redirect('mata-kuliah');
        }

        // Delete all related data
        // Delete all related data
        $pertemuan = $this->db->find('pertemuan', ['mata_kuliah_id' => $id]);
        $pertemuanIds = array_column($pertemuan, 'id');

        if (!empty($pertemuanIds)) {
            // Manual delete materi
            $materi = $this->db->all('materi');
            foreach ($materi as $m) {
                if (in_array($m['pertemuan_id'], $pertemuanIds)) {
                    $this->db->delete('materi', $m['id']);
                }
            }
            // Manual delete tugas
            $tugas = $this->db->all('tugas');
            foreach ($tugas as $t) {
                if (in_array($t['pertemuan_id'], $pertemuanIds)) {
                    $this->db->delete('tugas', $t['id']);
                }
            }
            // Delete pertemuan
            foreach ($pertemuanIds as $pid) {
                $this->db->delete('pertemuan', $pid);
            }
        }

        $this->db->delete('mata_kuliah', $id);

        flash('success', 'Mata kuliah berhasil dihapus');
        redirect('mata-kuliah');
    }
}
