<?php

/**
 * Admin Auth Controller
 */

namespace App\Controllers;

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

class AdminAuthController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Admin Login Page
     */
    public function login()
    {
        if (isset($_SESSION['admin'])) {
            redirect('admin/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $admin = $this->db->findOne('admin', ['username' => $username]);

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nama' => $admin['nama']
                ];

                flash('success', 'Login berhasil! Selamat datang, ' . $admin['nama']);
                redirect('admin/dashboard');
            } else {
                flash('error', 'Username atau password salah');
            }
        }

        view('admin.login');
    }
}
