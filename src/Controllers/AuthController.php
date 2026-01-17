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

        // Handle POST request for email/password login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                flash('error', 'Email dan password harus diisi');
                $authUrl = $this->googleAuth->getAuthUrl();
                view('auth.login', ['authUrl' => $authUrl]);
                return;
            }

            // Cari mahasiswa berdasarkan email
            $mahasiswa = $this->db->findOne('mahasiswa', ['email' => $email]);

            if (!$mahasiswa) {
                flash('error', 'Akun tidak ditemukan. Silakan login dengan Google terlebih dahulu.');
                $authUrl = $this->googleAuth->getAuthUrl();
                view('auth.login', ['authUrl' => $authUrl]);
                return;
            }

            // Cek apakah mahasiswa punya password
            if (empty($mahasiswa['password'])) {
                flash('error', 'Akun belum memiliki password. Silakan login dengan Google untuk membuat password.');
                $authUrl = $this->googleAuth->getAuthUrl();
                view('auth.login', ['authUrl' => $authUrl]);
                return;
            }

            // Verifikasi password
            if (!password_verify($password, $mahasiswa['password'])) {
                flash('error', 'Password salah');
                $authUrl = $this->googleAuth->getAuthUrl();
                view('auth.login', ['authUrl' => $authUrl]);
                return;
            }

            // Set session
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
            return;
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
            // Jika butuh NIM manual, simpan data sementara dan redirect ke form input NIM
            if (isset($studentInfo['need_nim']) && $studentInfo['need_nim']) {
                $_SESSION['pending_registration'] = [
                    'email' => $googleUser['email'],
                    'nama' => $googleUser['nama'],
                    'foto' => $googleUser['foto'],
                    'google_id' => $googleUser['google_id'],
                    'token' => $tokenResult['token'],
                    'refresh_token' => $tokenResult['refresh_token'],
                    'expires_in' => $tokenResult['expires_in']
                ];
                redirect('register-nim');
            }
            flash('error', $studentInfo['error']);
            redirect('login');
        }

        // 4. Cek apakah mahasiswa sudah terdaftar
        // 4. Cek apakah mahasiswa sudah terdaftar
        $mahasiswa = $this->db->findOne('mahasiswa', ['google_id' => $googleUser['google_id']]);
        if (!$mahasiswa) {
            $mahasiswa = $this->db->findOne('mahasiswa', ['email' => $googleUser['email']]);
        }

        if ($mahasiswa) {
            // Update existing mahasiswa
            // Update existing mahasiswa
            $this->db->update('mahasiswa', $mahasiswa['id'], [
                'google_id' => $googleUser['google_id'],
                'nama' => $googleUser['nama'],
                'foto' => $googleUser['foto'],
                'access_token' => json_encode($tokenResult['token']),
                'refresh_token' => $tokenResult['refresh_token'] ?? $mahasiswa['refresh_token'],
                'token_expires_at' => date('Y-m-d H:i:s', time() + $tokenResult['expires_in']),
                'semester_aktif' => $studentInfo['semester']
            ]);

            $mahasiswa['foto'] = $googleUser['foto'];
            $mahasiswa['semester_aktif'] = $studentInfo['semester'];
        } else {
            // Mahasiswa baru - simpan data sementara untuk konfirmasi password
            $_SESSION['pending_password'] = [
                'nim' => $studentInfo['nim'],
                'nama' => $googleUser['nama'],
                'email' => $googleUser['email'],
                'foto' => $googleUser['foto'],
                'google_id' => $googleUser['google_id'],
                'angkatan' => $studentInfo['angkatan'],
                'semester' => $studentInfo['semester'],
                'token' => $tokenResult['token'],
                'refresh_token' => $tokenResult['refresh_token'],
                'expires_in' => $tokenResult['expires_in']
            ];
            redirect('confirm-password');
            return;
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
     * Halaman konfirmasi password untuk user Google baru
     */
    public function confirmPassword()
    {
        // Cek apakah ada pending password
        if (!isset($_SESSION['pending_password'])) {
            redirect('login');
        }

        $pending = $_SESSION['pending_password'];

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';

            // Validasi password
            if (strlen($password) < 6) {
                flash('error', 'Password minimal 6 karakter');
                view('auth.confirm_password', ['pending' => $pending]);
                return;
            }

            if ($password !== $passwordConfirmation) {
                flash('error', 'Konfirmasi password tidak cocok');
                view('auth.confirm_password', ['pending' => $pending]);
                return;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Buat mahasiswa baru dengan password
            $mahasiswaId = $this->db->insert('mahasiswa', [
                'nim' => $pending['nim'],
                'nama' => $pending['nama'],
                'email' => $pending['email'],
                'google_id' => $pending['google_id'],
                'foto' => $pending['foto'],
                'password' => $hashedPassword,
                'angkatan' => $pending['angkatan'],
                'semester_aktif' => $pending['semester'],
                'access_token' => json_encode($pending['token']),
                'refresh_token' => $pending['refresh_token'],
                'token_expires_at' => date('Y-m-d H:i:s', time() + $pending['expires_in'])
            ]);

            // Buat root folder di Google Drive untuk mahasiswa baru
            $driveHelper = new GoogleDriveHelper($pending['token'], $pending['refresh_token']);
            $rootFolder = $driveHelper->createRootFolder($pending['nama']);

            if ($rootFolder['success']) {
                $this->db->update('mahasiswa', $mahasiswaId, [
                    'gdrive_folder_id' => $rootFolder['id']
                ]);
            }

            $mahasiswa = $this->db->findById('mahasiswa', $mahasiswaId);

            // Buat notifikasi selamat datang
            $this->db->insert('notifikasi', [
                'mahasiswa_id' => $mahasiswaId,
                'judul' => 'Selamat Datang di SIAPGRAK',
                'pesan' => 'Akun Anda telah berhasil dibuat. Mulai organisasikan materi kuliah Anda sekarang!',
                'tipe' => 'success',
                'link' => '/dashboard'
            ]);

            // Clear pending password
            unset($_SESSION['pending_password']);

            // Set session
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

            flash('success', 'Registrasi berhasil! Selamat datang, ' . $mahasiswa['nama']);
            redirect('dashboard');
            return;
        }

        view('auth.confirm_password', ['pending' => $pending]);
    }

    /**
     * Get current user's Google tokens
     */
    public function getTokens($mahasiswaId)
    {
        $mahasiswa = $this->db->findById('mahasiswa', $mahasiswaId);

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

    /**
     * Halaman input NIM manual
     */
    public function registerNim()
    {
        // Cek apakah ada pending registration
        if (!isset($_SESSION['pending_registration'])) {
            redirect('login');
        }

        $pending = $_SESSION['pending_registration'];

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nim = sanitize($_POST['nim'] ?? '');
            $no_hp = sanitize($_POST['no_hp'] ?? '');

            // Validasi NIM (harus 10 digit)
            if (!preg_match('/^\d{10}$/', $nim)) {
                flash('error', 'NIM harus 10 digit angka');
                view('auth.register_nim', ['pending' => $pending]);
                return;
            }

            // Validasi No HP
            if (!preg_match('/^62\d+$/', $no_hp)) {
                flash('error', 'Nomor HP harus diawali dengan 62 dan hanya berisi angka');
                view('auth.register_nim', ['pending' => $pending]);
                return;
            }

            // Hitung angkatan dan semester
            $angkatan = 2000 + intval(substr($nim, 0, 2));
            $semester = hitungSemester($nim);

            // Cek apakah NIM sudah terdaftar
            $existing = $this->db->findOne('mahasiswa', ['nim' => $nim]);
            if ($existing) {
                flash('error', 'NIM sudah terdaftar. Silakan hubungi admin jika ini adalah NIM Anda.');
                view('auth.register_nim', ['pending' => $pending]);
                return;
            }

            // Buat mahasiswa baru
            $mahasiswaId = $this->db->insert('mahasiswa', [
                'nim' => $nim,
                'nama' => $pending['nama'],
                'email' => $pending['email'],
                'no_hp' => $no_hp,
                'google_id' => $pending['google_id'],
                'foto' => $pending['foto'],
                'angkatan' => $angkatan,
                'semester_aktif' => $semester,
                'access_token' => json_encode($pending['token']),
                'refresh_token' => $pending['refresh_token'],
                'token_expires_at' => date('Y-m-d H:i:s', time() + $pending['expires_in'])
            ]);

            // Buat folder Google Drive
            $driveHelper = new GoogleDriveHelper($pending['token'], $pending['refresh_token']);
            $rootFolder = $driveHelper->createRootFolder($pending['nama']);

            if ($rootFolder['success']) {
                $this->db->update('mahasiswa', $mahasiswaId, [
                    'gdrive_folder_id' => $rootFolder['id']
                ]);
            }

            $mahasiswa = $this->db->findById('mahasiswa', $mahasiswaId);

            // Buat notifikasi selamat datang
            $this->db->insert('notifikasi', [
                'mahasiswa_id' => $mahasiswaId,
                'judul' => 'Selamat Datang di SIAPGRAK',
                'pesan' => 'Akun Anda telah berhasil dibuat. Mulai organisasikan materi kuliah Anda sekarang!',
                'tipe' => 'success',
                'link' => '/dashboard'
            ]);

            // Clear pending registration
            unset($_SESSION['pending_registration']);

            // Set session
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

            flash('success', 'Registrasi berhasil! Selamat datang, ' . $mahasiswa['nama']);
            redirect('dashboard');
        }

        view('auth.register_nim', ['pending' => $pending]);
    }
}
