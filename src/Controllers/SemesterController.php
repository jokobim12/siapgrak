<?php

/**
 * Semester Controller - Self Manage by Mahasiswa
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class SemesterController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Daftar semester milik mahasiswa
     */
    public function index()
    {
        $user = auth();

        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);

        // Calculate stats manually
        $allMatkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        $allPertemuan = $this->db->all('pertemuan');
        $allMateri = $this->db->all('materi');

        foreach ($semesters as &$sem) {
            // Count matkul
            $semMatkul = array_filter($allMatkul, function ($mk) use ($sem) {
                return $mk['semester_id'] == $sem['id'];
            });
            $sem['total_matkul'] = count($semMatkul);

            // Count materi via matkul -> pertemuan -> materi
            $semMatkulIds = array_column($semMatkul, 'id');
            $semPertemuan = array_filter($allPertemuan, function ($p) use ($semMatkulIds) {
                return in_array($p['mata_kuliah_id'], $semMatkulIds);
            });
            $semPertemuanIds = array_column($semPertemuan, 'id');

            $semMateri = array_filter($allMateri, function ($m) use ($semPertemuanIds) {
                return in_array($m['pertemuan_id'], $semPertemuanIds);
            });
            $sem['total_materi'] = count($semMateri);
        }

        // Sort desc
        usort($semesters, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        view('semester.index', [
            'semesters' => $semesters
        ]);
    }

    /**
     * Form tambah semester
     */
    public function tambah()
    {
        $user = auth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = sanitize($_POST['nama'] ?? '');

            if (empty($nama)) {
                flash('error', 'Nama semester harus diisi');
                redirect('semester/tambah');
            }

            $lastId = $this->db->insert('semester', [
                'nama' => $nama,
                'mahasiswa_id' => $user['id'],
                'is_active' => 1
            ]);

            // Set other semesters inactive
            $userSemesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);
            foreach ($userSemesters as $sem) {
                if ($sem['id'] != $lastId) {
                    $this->db->update('semester', $sem['id'], ['is_active' => 0]);
                }
            }

            flash('success', 'Semester berhasil ditambahkan');
            redirect('semester');
        }

        view('semester.tambah');
    }

    /**
     * Hapus semester
     */
    public function hapus()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            redirect('semester');
        }

        // Cek ownership
        $semester = $this->db->findOne('semester', ['id' => $id, 'mahasiswa_id' => $user['id']]);

        if (!$semester) {
            flash('error', 'Semester tidak ditemukan');
            redirect('semester');
        }

        // Hapus semua data terkait
        // Hapus semua data terkait
        $matkuls = $this->db->find('mata_kuliah', ['semester_id' => $id]);
        $matkulIds = array_column($matkuls, 'id');

        if (!empty($matkulIds)) {
            $allPertemuan = $this->db->all('pertemuan');
            $relatedPertemuan = array_filter($allPertemuan, function ($p) use ($matkulIds) {
                return in_array($p['mata_kuliah_id'], $matkulIds);
            });
            $pertemuanIds = array_column($relatedPertemuan, 'id');

            if (!empty($pertemuanIds)) {
                // Delete materi
                $allMateri = $this->db->all('materi');
                foreach ($allMateri as $m) {
                    if (in_array($m['pertemuan_id'], $pertemuanIds)) {
                        $this->db->delete('materi', $m['id']);
                    }
                }

                // Delete pertemuan
                foreach ($pertemuanIds as $pid) {
                    $this->db->delete('pertemuan', $pid);
                }
            }

            // Delete mata kuliah
            foreach ($matkulIds as $mid) {
                $this->db->delete('mata_kuliah', $mid);
            }
        }

        $this->db->delete('semester', $id);

        flash('success', 'Semester berhasil dihapus');
        redirect('semester');
    }

    /**
     * Set semester aktif
     */
    public function setAktif()
    {
        $user = auth();
        $id = $_GET['id'] ?? null;

        if ($id) {
            $userSemesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);
            foreach ($userSemesters as $sem) {
                $status = ($sem['id'] == $id) ? 1 : 0;
                $this->db->update('semester', $sem['id'], ['is_active' => $status]);
            }
            flash('success', 'Semester aktif berhasil diubah');
        }

        redirect('semester');
    }
}
