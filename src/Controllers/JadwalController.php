<?php

/**
 * Jadwal Controller
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
     * Halaman jadwal mingguan
     */
    public function index()
    {
        $user = auth();

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $jadwalByHari = [];

        foreach ($hariList as $hari) {
            $jadwalByHari[$hari] = $this->db->fetchAll(
                "SELECT j.*, mk.nama_mk, mk.kode_mk, mk.dosen, k.nama_kelas
                 FROM jadwal j
                 JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
                 JOIN kelas k ON k.id = j.kelas_id
                 JOIN kelas_mahasiswa km ON km.kelas_id = k.id
                 WHERE km.mahasiswa_id = ? AND j.hari = ?
                 ORDER BY j.jam_mulai ASC",
                [$user['id'], $hari]
            );
        }

        // Get today's date info
        $hariIni = strtolower(date('l'));
        $hariMap = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu'
        ];
        $hariIniId = $hariMap[$hariIni] ?? null;

        view('jadwal.index', [
            'jadwalByHari' => $jadwalByHari,
            'hariList' => $hariList,
            'hariIni' => $hariIniId
        ]);
    }
}
