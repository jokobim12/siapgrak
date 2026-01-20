<?php

/**
 * Admin Controller
 * Panel untuk mengelola data akademik
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = db();

        // Check if admin is logged in
        if (!isset($_SESSION['admin'])) {
            redirect('admin/login');
        }
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'mahasiswa' => $this->db->count('mahasiswa'),
            // 'semester' => $this->db->count('semester'),
            // 'kelas' => $this->db->count('kelas'),
            // 'mata_kuliah' => $this->db->count('mata_kuliah'),
            // 'materi' => $this->db->count('materi'),
            // 'tugas' => $this->db->count('tugas')
        ];

        $allMahasiswa = $this->db->all('mahasiswa');
        usort($allMahasiswa, function ($a, $b) {
            $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $timeB - $timeA;
        });
        $recentMahasiswa = array_slice($allMahasiswa, 0, 5);

        view('admin.dashboard', [
            'stats' => $stats,
            'recentMahasiswa' => $recentMahasiswa
        ]);
    }

    /**
     * Kelola Semester
     */
    /**
     * Kelola Semester - DISABLED (Self-Manage)
     */
    public function semester()
    {
        flash('error', 'Fitur ini tidak tersedia di mode Self-Manage');
        redirect('admin/dashboard');
    }

    /**
     * Kelola Kelas
     */
    /**
     * Kelola Kelas - DISABLED (Self-Manage)
     */
    public function kelas()
    {
        flash('error', 'Fitur ini tidak tersedia di mode Self-Manage');
        redirect('admin/dashboard');
    }

    /**
     * Kelola Mata Kuliah
     */
    /**
     * Kelola Mata Kuliah - DISABLED (Self-Manage)
     */
    public function mataKuliah()
    {
        flash('error', 'Fitur ini tidak tersedia di mode Self-Manage');
        redirect('admin/dashboard');
    }

    /**
     * Kelola Jadwal
     */
    /**
     * Kelola Jadwal - DISABLED (Self-Manage)
     */
    public function jadwal()
    {
        flash('error', 'Fitur ini tidak tersedia di mode Self-Manage');
        redirect('admin/dashboard');
    }

    /**
     * Kelola Mahasiswa Kelas (assign mahasiswa ke kelas)
     */
    /**
     * Kelola Mahasiswa Kelas - DISABLED (Self-Manage)
     */
    public function kelasMahasiswa()
    {
        flash('error', 'Fitur ini tidak tersedia di mode Self-Manage');
        redirect('admin/dashboard');
    }

    /**
     * Admin Logout
     */
    public function logout()
    {
        unset($_SESSION['admin']);
        redirect('admin/login');
    }
}
