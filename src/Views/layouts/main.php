<?php

/**
 * Layout Utama
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'SIAPGRAK' ?> - Sistem Organisasi Materi Kuliah</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS -->
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        @media (min-width: 1024px) {
            .sidebar-hidden {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php if (isLoggedIn()): ?>
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside id="sidebar" class="sidebar fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 shadow-sm lg:shadow-none">
                <!-- Logo -->
                <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200">
                    <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-xl gradient-primary flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold gradient-text">SIAPGRAK</span>
                    </a>
                    <button id="closeSidebar" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="p-4 space-y-1">
                    <a href="<?= base_url('dashboard') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'sidebar-link-active' : 'sidebar-link' ?>">
                        <i class="fas fa-home w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= base_url('mata-kuliah') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], 'mata-kuliah') ? 'sidebar-link-active' : 'sidebar-link' ?>">
                        <i class="fas fa-book w-5"></i>
                        <span>Mata Kuliah</span>
                    </a>
                    <a href="<?= base_url('tugas') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], 'tugas') ? 'sidebar-link-active' : 'sidebar-link' ?>">
                        <i class="fas fa-tasks w-5"></i>
                        <span>Tugas</span>
                    </a>
                    <a href="<?= base_url('jadwal') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'], 'jadwal') ? 'sidebar-link-active' : 'sidebar-link' ?>">
                        <i class="fas fa-calendar-alt w-5"></i>
                        <span>Jadwal</span>
                    </a>
                </nav>

                <!-- User Info -->
                <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <img src="<?= auth()['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()['nama']) ?>"
                            alt="Avatar"
                            class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate"><?= auth()['nama'] ?></p>
                            <p class="text-xs text-gray-500">Semester <?= auth()['semester_aktif'] ?></p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Overlay for mobile -->
            <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-20 lg:hidden hidden"></div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-h-screen">
                <!-- Header -->
                <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-10">
                    <div class="flex items-center gap-4">
                        <button id="openSidebar" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-bars text-gray-600"></i>
                        </button>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button id="notifBtn" class="relative p-2 rounded-lg hover:bg-gray-100">
                                <i class="fas fa-bell text-gray-600"></i>
                                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                                    <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                                        <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                                    </span>
                                <?php endif; ?>
                            </button>
                            <div id="notifDropdown" class="dropdown hidden">
                                <div class="px-4 py-2 border-b border-gray-200">
                                    <p class="font-medium text-gray-900">Notifikasi</p>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <?php if (isset($notifikasi) && !empty($notifikasi)): ?>
                                        <?php foreach ($notifikasi as $notif): ?>
                                            <a href="<?= base_url($notif['link'] ?? 'dashboard') ?>"
                                                class="block px-4 py-3 hover:bg-gray-50 <?= !$notif['is_read'] ? 'bg-blue-50' : '' ?>">
                                                <p class="text-sm font-medium text-gray-900"><?= $notif['judul'] ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= formatDateTime($notif['created_at']) ?></p>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="px-4 py-3 text-sm text-gray-500">Tidak ada notifikasi</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button id="profileBtn" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                                <img src="<?= auth()['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()['nama']) ?>"
                                    alt="Avatar"
                                    class="w-8 h-8 rounded-full object-cover">
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div id="profileDropdown" class="dropdown hidden">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="font-medium text-gray-900"><?= auth()['nama'] ?></p>
                                    <p class="text-xs text-gray-500"><?= auth()['email'] ?></p>
                                </div>
                                <a href="<?= base_url('logout') ?>" class="dropdown-item text-red-600">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                <?php if ($success = flash('success')): ?>
                    <div class="notification-success" id="flashSuccess">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle"></i>
                            <p><?= $success ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error = flash('error')): ?>
                    <div class="notification-error" id="flashError">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle"></i>
                            <p><?= $error ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Page Content -->
                <main class="flex-1 p-4 lg:p-6">
                    <?= $content ?? '' ?>
                </main>

                <!-- Footer -->
                <footer class="bg-white border-t border-gray-200 py-4 px-6">
                    <p class="text-center text-sm text-gray-500">
                        &copy; <?= date('Y') ?> SIAPGRAK - Politeknik Negeri Tanah Laut
                    </p>
                </footer>
            </div>
        </div>
    <?php else: ?>
        <?= $content ?? '' ?>
    <?php endif; ?>

    <!-- Scripts -->
    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', () => {
                sidebar.classList.remove('sidebar-hidden');
                sidebarOverlay.classList.remove('hidden');
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', () => {
                sidebar.classList.add('sidebar-hidden');
                sidebarOverlay.classList.add('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.add('sidebar-hidden');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // Dropdown Toggles
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('hidden');
                profileDropdown?.classList.add('hidden');
            });
        }

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('hidden');
                notifDropdown?.classList.add('hidden');
            });
        }

        document.addEventListener('click', () => {
            notifDropdown?.classList.add('hidden');
            profileDropdown?.classList.add('hidden');
        });

        // Auto-hide flash messages
        setTimeout(() => {
            const flash = document.getElementById('flashSuccess') || document.getElementById('flashError');
            if (flash) {
                flash.style.opacity = '0';
                flash.style.transition = 'opacity 0.5s';
                setTimeout(() => flash.remove(), 500);
            }
        }, 5000);
    </script>
</body>

</html>