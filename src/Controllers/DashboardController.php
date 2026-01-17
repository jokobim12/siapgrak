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

        // Monitor rate limit logic - TEMPORARILY DISABLED for debugging
        // if (!isset($_SESSION['last_notif_check']) || (time() - $_SESSION['last_notif_check'] > 600)) {
        if (true) {
            try {
                require_once ROOT_PATH . '/src/Services/NotificationService.php';
                require_once ROOT_PATH . '/src/Helpers/FonnteHelper.php';
                $notifService = new \App\Services\NotificationService();
                $notifService->checkDeadlinesAndSend();
                $_SESSION['last_notif_check'] = time();
            } catch (\Exception $e) {
                // Silently fail to not disrupt dashboard
                error_log("Notif Check Error: " . $e->getMessage());
            }
        }

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

        // Filter tugas mepet deadline (max 2 hari) dan belum selesai
        $now = time();
        $twoDaysLater = strtotime('+2 days');

        $priorityTugas = array_filter($userTugas, function ($t) use ($now, $twoDaysLater) {
            $deadline = !empty($t['deadline']) ? strtotime($t['deadline']) : false;
            $isCompleted = !empty($t['is_completed']) && $t['is_completed'];
            // Check if deadline is valid, not completed, and within next 2 days (or overdue)
            // Note: If deadline is passed, it is definitely <= 2 days later.
            return !$isCompleted && $deadline && $deadline <= $twoDaysLater;
        });

        // Enrich priority tugas with MK name
        foreach ($priorityTugas as &$tugas) {
            // Find MK via pertemuan
            foreach ($allPertemuan as $p) {
                if ($p['id'] == $tugas['pertemuan_id']) {
                    foreach ($allMatkul as $mk) {
                        if ($mk['id'] == $p['mata_kuliah_id']) {
                            $tugas['mk_nama'] = $mk['nama_mk'];
                            break 2;
                        }
                    }
                }
            }
            if (!isset($tugas['mk_nama'])) {
                $tugas['mk_nama'] = 'Mata Kuliah Tidak Dikenal';
            }
        }
        unset($tugas); // break reference

        // Sort by deadline (soonest first)
        usort($priorityTugas, function ($a, $b) {
            $timeA = !empty($a['deadline']) ? strtotime($a['deadline']) : 0;
            $timeB = !empty($b['deadline']) ? strtotime($b['deadline']) : 0;
            return $timeA - $timeB;
        });

        view('dashboard.index', [
            'mahasiswa' => $mahasiswa,
            'stats' => $stats,
            'priorityTugas' => $priorityTugas
        ]);
    }
}
