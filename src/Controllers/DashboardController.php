<?php

/**
 * Dashboard Controller - Updated for Self-Manage
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
     * Dashboard utama
     */
    public function index()
    {
        $user = auth();

        // Get mahasiswa data
        $mahasiswa = $this->db->findById('mahasiswa', $user['id']);

        // Get semesters
        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);

        // Get all data needed for stats
        $allMatkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        $allPertemuan = $this->db->all('pertemuan');
        $allMateri = $this->db->all('materi');
        $allTugas = $this->db->all('tugas');

        // Calculate stats manually
        $totalMatkul = count($allMatkul);

        // Filter pertemuan by user's matkul
        $userMatkulIds = array_column($allMatkul, 'id');
        $userPertemuan = array_filter($allPertemuan, function ($p) use ($userMatkulIds) {
            return in_array($p['mata_kuliah_id'], $userMatkulIds);
        });
        $userPertemuanIds = array_column($userPertemuan, 'id');

        // Filter materi by user's pertemuan
        $userMateri = array_filter($allMateri, function ($m) use ($userPertemuanIds) {
            return in_array($m['pertemuan_id'], $userPertemuanIds);
        });

        // Filter tugas by user's pertemuan
        $userTugas = array_filter($allTugas, function ($t) use ($userPertemuanIds) {
            return in_array($t['pertemuan_id'], $userPertemuanIds);
        });

        $stats = [
            'total_semester' => count($semesters),
            'total_matkul' => $totalMatkul,
            'total_materi' => count($userMateri),
            'total_tugas' => count($userTugas)
        ];

        // Get recent matkul
        // Sort by created_at desc
        usort($allMatkul, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $recentMatkul = array_slice($allMatkul, 0, 5);

        // Enrich recent matkul with semester name
        foreach ($recentMatkul as &$mk) {
            $sem = $this->db->findById('semester', $mk['semester_id']);
            $mk['semester_nama'] = $sem ? $sem['nama'] : '-';
        }

        view('dashboard.index', [
            'mahasiswa' => $mahasiswa,
            'stats' => $stats,
            'recentMatkul' => $recentMatkul
        ]);
    }
}
