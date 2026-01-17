<?php

/**
 * Jadwal Controller - Updated for Self-Manage
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class JadwalController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Jadwal mingguan
     */
    public function index()
    {
        $user = auth();

        // Get jadwal grouped by day
        // Get jadwal logic manually
        $allJadwal = $this->db->all('jadwal');
        $allMatkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        $userMatkulIds = array_column($allMatkul, 'id');

        $jadwal = [];
        foreach ($allJadwal as $j) {
            if (in_array($j['mata_kuliah_id'], $userMatkulIds)) {
                // Find MK name
                foreach ($allMatkul as $mk) {
                    if ($mk['id'] == $j['mata_kuliah_id']) {
                        $j['nama_mk'] = $mk['nama_mk'];
                        break;
                    }
                }
                $jadwal[] = $j;
            }
        }

        // Custom sort by day and time
        usort($jadwal, function ($a, $b) {
            $days = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6];
            $dayOrder = $days[strtolower($a['hari'])] - $days[strtolower($b['hari'])];
            if ($dayOrder !== 0) return $dayOrder;

            return strtotime($a['jam_mulai']) - strtotime($b['jam_mulai']);
        });

        // Group by day
        $jadwalByDay = [
            'senin' => [],
            'selasa' => [],
            'rabu' => [],
            'kamis' => [],
            'jumat' => [],
            'sabtu' => []
        ];

        foreach ($jadwal as $j) {
            $jadwalByDay[$j['hari']][] = $j;
        }

        view('jadwal.index', [
            'jadwalByDay' => $jadwalByDay
        ]);
    }
}
