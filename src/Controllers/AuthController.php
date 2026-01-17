<?php

/**
 * Auth Controller
 * Menangani login, logout, dan callback Google OAuth
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/src/Helpers/GoogleAuthHelper.php';
require_once ROOT_PATH . '/src/Helpers/GoogleDriveHelper.php';

use App\Helpers\GoogleAuthHelper;
use App\Helpers\GoogleDriveHelper;

class AuthController
{
    private $db;
    private $googleAuth;

    public function __construct()
    {
        $this->db = db();
        $this->googleAuth = new GoogleAuthHelper();
    }

    /**
     * Halaman login
     */
    public function login()
    {
        if (isLoggedIn()) {
            redirect('dashboard');
        }

        $authUrl = $this->googleAuth->getAuthUrl();
        view('auth.login', ['authUrl' => $authUrl]);
    }

    /**
     * Google OAuth callback
     */
    public function callback()
    {
        if (!isset($_GET['code'])) {
            flash('error', 'Autentikasi gagal. Silakan coba lagi.');
            redirect('login');
        }

        $code = $_GET['code'];

        // 1. Get access token dari Google
        $tokenResult = $this->googleAuth->getAccessToken($code);

        if (!$tokenResult['success']) {
            flash('error', 'Gagal mendapatkan token: ' . $tokenResult['error']);
            redirect('login');
        }

        // 2. Get user info dari Google
        $userResult = $this->googleAuth->getUserInfo($tokenResult['token']);

        if (!$userResult['success']) {
            flash('error', 'Gagal mendapatkan info user: ' . $userResult['error']);
            redirect('login');
        }

        $googleUser = $userResult['user'];

        // 3. Validasi email mahasiswa
        $studentInfo = $this->googleAuth->extractStudentInfo(
            $googleUser['email'],
            $googleUser['nama']
        );

        if (!$studentInfo['success']) {
            flash('error', $studentInfo['error']);
            redirect('login');
        }

        // 4. Cek apakah mahasiswa sudah terdaftar
        $mahasiswa = $this->db->fetch(
            "SELECT * FROM mahasiswa WHERE google_id = ? OR email = ?",
            [$googleUser['google_id'], $googleUser['email']]
        );

        if ($mahasiswa) {
            // Update existing mahasiswa
            $this->db->update('mahasiswa', [
                'google_id' => $googleUser['google_id'],
                'nama' => $googleUser['nama'],
                'foto' => $googleUser['foto'],
                'access_token' => json_encode($tokenResult['token']),
                'refresh_token' => $tokenResult['refresh_token'] ?? $mahasiswa['refresh_token'],
                'token_expires_at' => date('Y-m-d H:i:s', time() + $tokenResult['expires_in']),
                'semester_aktif' => $studentInfo['semester']
            ], 'id = ?', [$mahasiswa['id']]);

            $mahasiswa['foto'] = $googleUser['foto'];
            $mahasiswa['semester_aktif'] = $studentInfo['semester'];
        } else {
            // Buat mahasiswa baru
            $mahasiswaId = $this->db->insert('mahasiswa', [
                'nim' => $studentInfo['nim'],
                'nama' => $googleUser['nama'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['google_id'],
                'foto' => $googleUser['foto'],
                'angkatan' => $studentInfo['angkatan'],
                'semester_aktif' => $studentInfo['semester'],
                'access_token' => json_encode($tokenResult['token']),
                'refresh_token' => $tokenResult['refresh_token'],
                'token_expires_at' => date('Y-m-d H:i:s', time() + $tokenResult['expires_in'])
            ]);

            // Buat root folder di Google Drive untuk mahasiswa baru
            $driveHelper = new GoogleDriveHelper($tokenResult['token'], $tokenResult['refresh_token']);
            $rootFolder = $driveHelper->createRootFolder($googleUser['nama']);

            if ($rootFolder['success']) {
                $this->db->update('mahasiswa', [
                    'gdrive_folder_id' => $rootFolder['id']
                ], 'id = ?', [$mahasiswaId]);
            }

            $mahasiswa = $this->db->fetch("SELECT * FROM mahasiswa WHERE id = ?", [$mahasiswaId]);

            // Buat notifikasi selamat datang
            $this->db->insert('notifikasi', [
                'mahasiswa_id' => $mahasiswaId,
                'judul' => 'Selamat Datang di SIAPGRAK',
                'pesan' => 'Akun Anda telah berhasil dibuat. Mulai organisasikan materi kuliah Anda sekarang!',
                'tipe' => 'success',
                'link' => '/dashboard'
            ]);
        }

        // 5. Set session
        $_SESSION['user'] = [
            'id' => $mahasiswa['id'],
            'nim' => $mahasiswa['nim'],
            'nama' => $mahasiswa['nama'],
            'email' => $mahasiswa['email'],
            'foto' => $mahasiswa['foto'],
            'angkatan' => $mahasiswa['angkatan'],
            'semester_aktif' => $mahasiswa['semester_aktif'],
            'gdrive_folder_id' => $mahasiswa['gdrive_folder_id']
        ];

        flash('success', 'Login berhasil! Selamat datang, ' . $mahasiswa['nama']);
        redirect('dashboard');
    }

    /**
     * Logout
     */
    public function logout()
    {
        unset($_SESSION['user']);
        session_destroy();
        redirect('login');
    }

    /**
     * Get current user's Google tokens
     */
    public function getTokens($mahasiswaId)
    {
        $mahasiswa = $this->db->fetch(
            "SELECT access_token, refresh_token, token_expires_at FROM mahasiswa WHERE id = ?",
            [$mahasiswaId]
        );

        if (!$mahasiswa) {
            return null;
        }

        $accessToken = json_decode($mahasiswa['access_token'], true);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $mahasiswa['refresh_token'],
            'expires_at' => $mahasiswa['token_expires_at']
        ];
    }
}
