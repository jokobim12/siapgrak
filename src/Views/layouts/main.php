<?php

/**
 * Main Layout - Mobile Friendly
 */
$user = auth();
$currentPage = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <title><?= $title ?? 'SIAPGRAK' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
    <style>
        .sidebar-open {
            transform: translateX(0);
        }

        .sidebar-closed {
            transform: translateX(-100%);
        }

        @media (min-width: 1024px) {
            .sidebar-closed {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 h-14 bg-white border-b border-gray-200 z-30 flex items-center px-4">
        <button onclick="toggleSidebar()" class="p-2 -ml-2 text-gray-600">
            <i class="fas fa-bars text-lg"></i>
        </button>
        <span class="ml-3 font-semibold text-primary-600">SIAPGRAK</span>
        <div class="ml-auto flex items-center gap-2">
            <a href="<?= base_url('notification') ?>" class="p-2 text-gray-600 relative">
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </header>

    <!-- Overlay -->
    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-72 bg-white border-r border-gray-200 z-40 transition-transform duration-300 sidebar-closed lg:sidebar-open">
        <div class="h-14 flex items-center px-4 border-b border-gray-200">
            <i class="fas fa-graduation-cap text-xl text-primary-600 mr-3"></i>
            <span class="font-bold text-lg text-gray-800">SIAPGRAK</span>
        </div>

        <nav class="p-3 space-y-1">
            <a href="<?= base_url('dashboard') ?>" class="nav-item <?= strpos($currentPage, 'dashboard') !== false ? 'active' : '' ?>">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= base_url('semester') ?>" class="nav-item <?= strpos($currentPage, 'semester') !== false ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Semester</span>
            </a>
            <a href="<?= base_url('mata-kuliah') ?>" class="nav-item <?= strpos($currentPage, 'mata-kuliah') !== false ? 'active' : '' ?>">
                <i class="fas fa-book w-5"></i>
                <span>Mata Kuliah</span>
            </a>
            <a href="<?= base_url('tugas') ?>" class="nav-item <?= strpos($currentPage, 'tugas') !== false ? 'active' : '' ?>">
                <i class="fas fa-tasks w-5"></i>
                <span>Tugas</span>
            </a>
            <a href="<?= base_url('jadwal') ?>" class="nav-item <?= strpos($currentPage, 'jadwal') !== false ? 'active' : '' ?>">
                <i class="fas fa-clock w-5"></i>
                <span>Jadwal</span>
            </a>
        </nav>

        <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200">
            <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50">
                <img src="<?= $user['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) ?>"
                    alt="<?= $user['nama'] ?>" class="w-10 h-10 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate"><?= $user['nama'] ?></p>
                    <p class="text-xs text-gray-500"><?= $user['nim'] ?></p>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="mt-2 flex items-center justify-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-72 pt-14 lg:pt-0 min-h-screen">
        <div class="p-4 lg:p-6 max-w-5xl mx-auto">
            <!-- Flash Messages -->
            <?php if ($success = flash('success')): ?>
                <div class="alert-success mb-4">
                    <i class="fas fa-check-circle flex-shrink-0"></i>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error = flash('error')): ?>
                <div class="alert-error mb-4">
                    <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </div>
    </main>

    <!-- Bottom Nav for Mobile -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-30 safe-area-inset">
        <div class="flex items-center justify-around h-14">
            <a href="<?= base_url('dashboard') ?>" class="flex flex-col items-center justify-center flex-1 py-2 <?= strpos($currentPage, 'dashboard') !== false ? 'text-primary-600' : 'text-gray-500' ?>">
                <i class="fas fa-home text-lg"></i>
                <span class="text-xs mt-1">Home</span>
            </a>
            <a href="<?= base_url('mata-kuliah') ?>" class="flex flex-col items-center justify-center flex-1 py-2 <?= strpos($currentPage, 'mata-kuliah') !== false ? 'text-primary-600' : 'text-gray-500' ?>">
                <i class="fas fa-book text-lg"></i>
                <span class="text-xs mt-1">Matkul</span>
            </a>
            <a href="<?= base_url('tugas') ?>" class="flex flex-col items-center justify-center flex-1 py-2 <?= strpos($currentPage, 'tugas') !== false ? 'text-primary-600' : 'text-gray-500' ?>">
                <i class="fas fa-tasks text-lg"></i>
                <span class="text-xs mt-1">Tugas</span>
            </a>
            <a href="<?= base_url('jadwal') ?>" class="flex flex-col items-center justify-center flex-1 py-2 <?= strpos($currentPage, 'jadwal') !== false ? 'text-primary-600' : 'text-gray-500' ?>">
                <i class="fas fa-clock text-lg"></i>
                <span class="text-xs mt-1">Jadwal</span>
            </a>
        </div>
    </nav>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('sidebar-open');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }
    </script>
</body>

</html>