<?php

/**
 * SIAPGRAK - Entry Point
 * Sistem Organisasi Materi Kuliah
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) !== 'index.php') {
    die('Direct access not allowed');
}

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Autoload controllers
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Simple Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Define routes
$routes = [
    // Auth routes
    '' => ['controller' => 'AuthController', 'action' => 'login'],
    'login' => ['controller' => 'AuthController', 'action' => 'login'],
    'auth/callback' => ['controller' => 'AuthController', 'action' => 'callback'],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    'register-nim' => ['controller' => 'AuthController', 'action' => 'registerNim'],
    'confirm-password' => ['controller' => 'AuthController', 'action' => 'confirmPassword'],

    // Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'auth' => true],

    // Semester (Self-manage by mahasiswa)
    'semester' => ['controller' => 'SemesterController', 'action' => 'index', 'auth' => true],
    'semester/tambah' => ['controller' => 'SemesterController', 'action' => 'tambah', 'auth' => true],
    'semester/hapus' => ['controller' => 'SemesterController', 'action' => 'hapus', 'auth' => true],
    'semester/set-aktif' => ['controller' => 'SemesterController', 'action' => 'setAktif', 'auth' => true],

    // Mata Kuliah (Self-manage by mahasiswa)
    'mata-kuliah' => ['controller' => 'MataKuliahController', 'action' => 'index', 'auth' => true],
    'mata-kuliah/tambah' => ['controller' => 'MataKuliahController', 'action' => 'tambah', 'auth' => true],
    'mata-kuliah/detail' => ['controller' => 'MataKuliahController', 'action' => 'detail', 'auth' => true],
    'mata-kuliah/hapus' => ['controller' => 'MataKuliahController', 'action' => 'hapus', 'auth' => true],

    // Pertemuan
    'pertemuan' => ['controller' => 'PertemuanController', 'action' => 'detail', 'auth' => true],
    'pertemuan/upload' => ['controller' => 'PertemuanController', 'action' => 'uploadMateri', 'auth' => true, 'method' => 'POST'],
    'pertemuan/delete-materi' => ['controller' => 'PertemuanController', 'action' => 'deleteMateri', 'auth' => true, 'method' => 'POST'],

    // Tugas
    'tugas' => ['controller' => 'TugasController', 'action' => 'index', 'auth' => true],
    'tugas/detail' => ['controller' => 'TugasController', 'action' => 'detail', 'auth' => true],
    'tugas/submit' => ['controller' => 'TugasController', 'action' => 'submit', 'auth' => true, 'method' => 'POST'],
    'tugas/create' => ['controller' => 'TugasController', 'action' => 'create', 'auth' => true, 'method' => 'POST'],
    'tugas/delete' => ['controller' => 'TugasController', 'action' => 'delete', 'auth' => true, 'method' => 'POST'],
    'tugas/uncheck' => ['controller' => 'TugasController', 'action' => 'uncheck', 'auth' => true, 'method' => 'POST'],
    'tugas/update' => ['controller' => 'TugasController', 'action' => 'update', 'auth' => true, 'method' => 'POST'],
    'tugas/upload' => ['controller' => 'TugasController', 'action' => 'upload', 'auth' => true, 'method' => 'POST'],

    // Profile
    'profile' => ['controller' => 'ProfileController', 'action' => 'index', 'auth' => true],
    'profile/update' => ['controller' => 'ProfileController', 'action' => 'update', 'auth' => true, 'method' => 'POST'],

    // Jadwal
    'jadwal' => ['controller' => 'JadwalController', 'action' => 'index', 'auth' => true],
    'jadwal/tambah' => ['controller' => 'JadwalController', 'action' => 'create', 'auth' => true],
    'jadwal/simpan' => ['controller' => 'JadwalController', 'action' => 'store', 'auth' => true, 'method' => 'POST'],
    'jadwal/hapus' => ['controller' => 'JadwalController', 'action' => 'delete', 'auth' => true, 'method' => 'POST'],

    // Search
    'search' => ['controller' => 'SearchController', 'action' => 'search', 'auth' => true],

    // Admin Auth
    'admin/login' => ['controller' => 'AdminAuthController', 'action' => 'login'],

    // Admin Panel
    'admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard', 'admin' => true],
    'admin/semester' => ['controller' => 'AdminController', 'action' => 'semester', 'admin' => true],
    'admin/kelas' => ['controller' => 'AdminController', 'action' => 'kelas', 'admin' => true],
    'admin/mata-kuliah' => ['controller' => 'AdminController', 'action' => 'mataKuliah', 'admin' => true],
    'admin/jadwal' => ['controller' => 'AdminController', 'action' => 'jadwal', 'admin' => true],
    'admin/kelas-mahasiswa' => ['controller' => 'AdminController', 'action' => 'kelasMahasiswa', 'admin' => true],
    'admin/logout' => ['controller' => 'AdminController', 'action' => 'logout', 'admin' => true],
];

// Handle routing
$route = $routes[$uri] ?? null;

if ($route) {
    // Check method
    if (isset($route['method']) && $route['method'] !== $method) {
        http_response_code(405);
        die('Method not allowed');
    }

    // Check authentication
    if (isset($route['auth']) && $route['auth'] && !isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu');
        redirect('login');
    }

    // Check admin authentication
    if (isset($route['admin']) && $route['admin'] && !isset($_SESSION['admin'])) {
        flash('error', 'Silakan login sebagai admin');
        redirect('admin/login');
    }

    // Redirect logged in users from login page
    if ($uri === 'login' && isLoggedIn()) {
        redirect('dashboard');
    }

    // Load and execute controller
    $controllerName = "App\\Controllers\\" . $route['controller'];
    $action = $route['action'];

    $controllerFile = ROOT_PATH . '/src/Controllers/' . $route['controller'] . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            if (method_exists($controller, $action)) {
                $controller->$action();
            } else {
                http_response_code(404);
                view('errors.404');
            }
        } else {
            http_response_code(500);
            die('Controller class not found: ' . $controllerName);
        }
    } else {
        http_response_code(500);
        die('Controller file not found: ' . $route['controller']);
    }
} else {
    // 404 Not Found
    http_response_code(404);
    if (file_exists(VIEWS_PATH . '/errors/404.php')) {
        view('errors.404');
    } else {
        echo '<h1>404 - Halaman tidak ditemukan</h1>';
        echo '<p><a href="' . base_url() . '">Kembali ke beranda</a></p>';
    }
}
