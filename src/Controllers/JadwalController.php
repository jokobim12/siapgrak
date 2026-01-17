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

        // Get semester aktif
        $semesterAktif = $this->db->findOne('semester', [
            'mahasiswa_id' => $user['id'],
            'is_aktif' => true
        ]);

        // Get jadwal grouped by day
        $allJadwal = $this->db->all('jadwal');

        // Get mata kuliah hanya dari semester aktif
        $filterMatkul = ['mahasiswa_id' => $user['id']];
        if ($semesterAktif) {
            $filterMatkul['semester_id'] = $semesterAktif['id'];
        }
        $allMatkul = $this->db->find('mata_kuliah', $filterMatkul);
        $userMatkulIds = array_column($allMatkul, 'id');

        $jadwal = [];
        foreach ($allJadwal as $j) {
            if (in_array($j['mata_kuliah_id'], $userMatkulIds)) {
                // Find MK name
                foreach ($allMatkul as $mk) {
                    if ($mk['id'] == $j['mata_kuliah_id']) {
                        $j['nama_mk'] = $mk['nama_mk'];
                        $j['dosen'] = $mk['dosen'] ?? '-';
                        $j['korti'] = $mk['korti'] ?? '-';
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
            'jadwalByDay' => $jadwalByDay,
            'semesterAktif' => $semesterAktif
        ]);
    }
    public function create()
    {
        $user = auth();
        // Get user's MK for dropdown
        $matkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        // Get semesters for filter
        $semesters = $this->db->find('semester', ['mahasiswa_id' => $user['id']]);

        // Sort semesters by number ascending
        usort($semesters, function ($a, $b) {
            preg_match('/(\d+)/', $a['nama'], $matchA);
            preg_match('/(\d+)/', $b['nama'], $matchB);
            return (int)($matchA[1] ?? 0) - (int)($matchB[1] ?? 0);
        });

        // Get existing jadwal for validation
        $allJadwal = $this->db->all('jadwal');
        $userMatkulIds = array_column($matkul, 'id');
        $scheduledMatkulIds = [];
        $activeSemesterId = null;

        foreach ($allJadwal as $j) {
            if (in_array($j['mata_kuliah_id'], $userMatkulIds)) {
                $scheduledMatkulIds[] = $j['mata_kuliah_id'];
                // Get the semester of this scheduled matkul
                foreach ($matkul as $mk) {
                    if ($mk['id'] == $j['mata_kuliah_id'] && !$activeSemesterId) {
                        $activeSemesterId = $mk['semester_id'];
                    }
                }
            }
        }
        $scheduledMatkulIds = array_unique($scheduledMatkulIds);

        view('jadwal.create', [
            'matkul' => $matkul,
            'semesters' => $semesters,
            'scheduledMatkulIds' => $scheduledMatkulIds,
            'activeSemesterId' => $activeSemesterId
        ]);
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
        $dosen = sanitize($_POST['dosen'] ?? '');
        $korti = sanitize($_POST['korti'] ?? '');

        if (!$mata_kuliah_id || !$hari || !$jam_mulai || !$jam_selesai) {
            flash('error', 'Data wajib diisi');
            redirect('jadwal/tambah');
        }

        // Validation 1: Check if matkul already scheduled
        $existingJadwal = $this->db->findOne('jadwal', ['mata_kuliah_id' => $mata_kuliah_id]);
        if ($existingJadwal) {
            flash('error', 'Mata kuliah ini sudah dijadwalkan. Satu mata kuliah hanya bisa dijadwalkan sekali.');
            redirect('jadwal/tambah');
        }

        // Validation 2: Check semester consistency
        $matkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        $userMatkulIds = array_column($matkul, 'id');
        $allJadwal = $this->db->all('jadwal');

        $newMk = $this->db->findById('mata_kuliah', $mata_kuliah_id);
        $newSemesterId = $newMk['semester_id'] ?? null;

        foreach ($allJadwal as $j) {
            if (in_array($j['mata_kuliah_id'], $userMatkulIds)) {
                foreach ($matkul as $mk) {
                    if ($mk['id'] == $j['mata_kuliah_id'] && $mk['semester_id'] != $newSemesterId) {
                        flash('error', 'Jadwal hanya boleh dari satu semester yang sama. Hapus jadwal lama atau pilih mata kuliah dari semester yang sama.');
                        redirect('jadwal/tambah');
                    }
                }
            }
        }

        // Save Jadwal
        $this->db->insert('jadwal', [
            'mata_kuliah_id' => $mata_kuliah_id,
            'hari' => $hari,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai,
            'ruangan' => $ruangan
        ]);

        // Update MK with Dosen and Korti
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
