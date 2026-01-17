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
                        $j['dosen'] = $mk['dosen'] ?? '-';
                        $j['korti'] = $mk['korti'] ?? '-'; // Fetch Korti
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
    public function create()
    {
        $user = auth();
        // Get user's MK for dropdown
        $matkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        // Get semesters for filter
        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);
        view('jadwal.create', ['matkul' => $matkul, 'semesters' => $semesters]);
    }

    public function store()
    {
        $user = auth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('jadwal');
        }

        $mata_kuliah_id = $_POST['mata_kuliah_id'] ?? null;
        $hari = $_POST['hari'] ?? '';
        $jam_mulai = $_POST['jam_mulai'] ?? '';
        $jam_selesai = $_POST['jam_selesai'] ?? '';
        $ruangan = sanitize($_POST['ruangan'] ?? '');

        // Optional: Update MK detail like Dosen and Korti if provided
        $dosen = sanitize($_POST['dosen'] ?? '');
        $korti = sanitize($_POST['korti'] ?? '');

        if (!$mata_kuliah_id || !$hari || !$jam_mulai || !$jam_selesai) {
            flash('error', 'Data wajib diisi');
            redirect('jadwal/tambah');
        }

        // Save Jadwal
        $this->db->insert('jadwal', [
            'mata_kuliah_id' => $mata_kuliah_id,
            'hari' => $hari,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai,
            'ruangan' => $ruangan
        ]);

        // Update MK with Dosen and potentially Korti (if we add that column)
        // Since we are using JSON Database, we can just add keys.
        // Let's check if MK exists
        $mk = $this->db->findById('mata_kuliah', $mata_kuliah_id);
        if ($mk) {
            $updateData = [];
            if (!empty($dosen)) $updateData['dosen'] = $dosen;
            if (!empty($korti)) $updateData['korti'] = $korti;

            if (!empty($updateData)) {
                $this->db->update('mata_kuliah', $mata_kuliah_id, $updateData);
            }
        }

        flash('success', 'Jadwal berhasil ditambahkan');
        redirect('jadwal');
    }

    public function delete()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $this->db->delete('jadwal', $id);
            flash('success', 'Jadwal dihapus');
        }
        redirect('jadwal');
    }
}
