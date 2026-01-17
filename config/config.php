<?php

/**
 * SIAPGRAK - Sistem Organisasi Materi Kuliah
 * File Konfigurasi Utama
 */

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'SIAPGRAK');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'siapgrak');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '1234');

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? APP_URL . '/auth/callback');
// Fonnte Configuration
define('FONNTE_TOKEN', $_ENV['FONNTE_TOKEN'] ?? '');
// Session Configuration
define('SESSION_LIFETIME', intval($_ENV['SESSION_LIFETIME'] ?? 120));

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('SRC_PATH', ROOT_PATH . '/src');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting based on environment
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Makassar');

// Helper functions
function base_url($path = '')
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset($path)
{
    return base_url('css/' . ltrim($path, '/'));
}

function redirect($path)
{
    header('Location: ' . base_url($path));
    exit;
}

function view($name, $data = [])
{
    extract($data);
    $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $name) . '.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        throw new Exception("View not found: $name");
    }
}

function old($key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function flash($key, $value = null)
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
    } else {
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}

function auth()
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date, $format = 'd M Y')
{
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

function formatDateTime($date, $format = 'd M Y H:i')
{
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function getFileIcon($extension)
{
    $icons = [
        'pdf' => 'fa-file-pdf text-red-500',
        'doc' => 'fa-file-word text-blue-500',
        'docx' => 'fa-file-word text-blue-500',
        'xls' => 'fa-file-excel text-green-500',
        'xlsx' => 'fa-file-excel text-green-500',
        'ppt' => 'fa-file-powerpoint text-orange-500',
        'pptx' => 'fa-file-powerpoint text-orange-500',
        'jpg' => 'fa-file-image text-purple-500',
        'jpeg' => 'fa-file-image text-purple-500',
        'png' => 'fa-file-image text-purple-500',
        'gif' => 'fa-file-image text-purple-500',
        'mp4' => 'fa-file-video text-pink-500',
        'avi' => 'fa-file-video text-pink-500',
        'mov' => 'fa-file-video text-pink-500',
        'zip' => 'fa-file-archive text-yellow-500',
        'rar' => 'fa-file-archive text-yellow-500',
    ];
    return $icons[strtolower($extension)] ?? 'fa-file text-gray-500';
}

/**
 * Menghitung semester berdasarkan NIM
 * Format NIM: 24XXXXXXX (2 digit pertama = tahun angkatan)
 */
function hitungSemester($nim)
{
    $tahun_angkatan = 2000 + intval(substr($nim, 0, 2));
    $tahun_sekarang = intval(date('Y'));
    $bulan_sekarang = intval(date('n'));

    // Semester ganjil: September - Februari (bulan 9-2)
    // Semester genap: Maret - Agustus (bulan 3-8)

    $tahun_berjalan = $tahun_sekarang - $tahun_angkatan;

    // Jika bulan sekarang >= September, berarti sudah masuk semester ganjil tahun berikutnya
    if ($bulan_sekarang >= 9) {
        $semester = ($tahun_berjalan * 2) + 1;
    } elseif ($bulan_sekarang >= 3) {
        // Maret - Agustus = semester genap
        $semester = $tahun_berjalan * 2;
    } else {
        // Januari - Februari = masih semester ganjil tahun sebelumnya
        $semester = (($tahun_berjalan - 1) * 2) + 1;
    }

    return max(1, $semester);
}

/**
 * Ekstrak NIM dari email Politala
 * Format: 2401301001@mhs.politala.ac.id atau 2401301001 Joko bimantaro@mhs.politala.ac.id
 */
function extractNIM($email)
{
    // Pattern untuk email Politala
    if (preg_match('/^(\d{10})/', $email, $matches)) {
        return $matches[1];
    }
    return null;
}
