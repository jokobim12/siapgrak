<?php

/**
 * Search Controller - Global Search
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class SearchController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Search API endpoint - returns JSON
     */
    public function search()
    {
        $user = auth();
        $query = trim($_GET['q'] ?? '');

        header('Content-Type: application/json');

        if (strlen($query) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }

        $results = [];
        $queryLower = strtolower($query);

        // Search Mata Kuliah
        $allMatkul = $this->db->find('mata_kuliah', ['mahasiswa_id' => $user['id']]);
        foreach ($allMatkul as $mk) {
            if (stripos($mk['nama_mk'], $query) !== false) {
                $results[] = [
                    'type' => 'matkul',
                    'icon' => 'fa-book',
                    'title' => $mk['nama_mk'],
                    'subtitle' => 'Mata Kuliah',
                    'url' => base_url('mata-kuliah/detail?id=' . $mk['id'])
                ];
            }
        }

        // Search Materi
        $allMateri = $this->db->all('materi');
        $allPertemuan = $this->db->all('pertemuan');
        $userMkIds = array_column($allMatkul, 'id');

        foreach ($allMateri as $m) {
            if (stripos($m['judul'], $query) !== false || stripos($m['nama_file'] ?? '', $query) !== false) {
                // Find pertemuan and check ownership
                foreach ($allPertemuan as $p) {
                    if ($p['id'] == $m['pertemuan_id'] && in_array($p['mata_kuliah_id'], $userMkIds)) {
                        $mkName = '';
                        foreach ($allMatkul as $mk) {
                            if ($mk['id'] == $p['mata_kuliah_id']) {
                                $mkName = $mk['nama_mk'];
                                break;
                            }
                        }
                        $results[] = [
                            'type' => 'materi',
                            'icon' => 'fa-file-alt',
                            'title' => $m['judul'] ?: $m['nama_file'],
                            'subtitle' => "Materi · $mkName · P{$p['nomor_pertemuan']}",
                            'url' => base_url('pertemuan?id=' . $p['id'])
                        ];
                        break;
                    }
                }
            }
        }

        // Search Tugas (Todo items only, not file uploads)
        $allTugas = $this->db->all('tugas');
        foreach ($allTugas as $t) {
            if (!empty($t['tipe_file'])) continue; // Skip file uploads

            if (stripos($t['judul'], $query) !== false) {
                foreach ($allPertemuan as $p) {
                    if ($p['id'] == $t['pertemuan_id'] && in_array($p['mata_kuliah_id'], $userMkIds)) {
                        $mkName = '';
                        foreach ($allMatkul as $mk) {
                            if ($mk['id'] == $p['mata_kuliah_id']) {
                                $mkName = $mk['nama_mk'];
                                break;
                            }
                        }
                        $results[] = [
                            'type' => 'tugas',
                            'icon' => 'fa-tasks',
                            'title' => $t['judul'],
                            'subtitle' => "Tugas · $mkName",
                            'url' => base_url('tugas/detail?id=' . $t['id'])
                        ];
                        break;
                    }
                }
            }
        }

        // Limit results
        $results = array_slice($results, 0, 10);

        echo json_encode(['results' => $results]);
        exit;
    }
}
