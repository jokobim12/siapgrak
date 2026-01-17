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
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
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
            <a href="<?= base_url('profile') ?>" class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer group">
                <img src="<?= $user['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) ?>"
                    alt="<?= $user['nama'] ?>" class="w-10 h-10 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate group-hover:text-primary-600"><?= $user['nama'] ?></p>
                    <p class="text-xs text-gray-500"><?= $user['nim'] ?></p>
                </div>
                <div class="text-gray-400">
                    <i class="fas fa-chevron-right text-xs"></i>
                </div>
            </a>
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

    <!-- Modern Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity opacity-0" id="confirmBackdrop"></div>

        <!-- Modal Card -->
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm transform scale-95 opacity-0 transition-all duration-200 relative z-10" id="confirmCard">
            <div class="p-6 text-center">
                <!-- Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-circle text-2xl text-red-600"></i>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2" id="confirmTitle">Konfirmasi</h3>
                <p class="text-sm text-gray-500 mb-6" id="confirmMessage">Apakah Anda yakin ingin melakukan tindakan ini?</p>

                <div class="flex gap-3">
                    <button id="confirmCancelBtn" class="flex-1 px-4 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                        Batal
                    </button>
                    <a id="confirmOkBtn" href="#" class="flex-1 px-4 py-2.5 bg-red-600 border border-transparent rounded-xl text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-sm">
                        Ya, Lanjutkan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('sidebar-open');
            sidebar.classList.toggle('sidebar-closed');
            overlay.classList.toggle('hidden');
        }

        // Confirmation Modal Logic
        const confirmModal = document.getElementById('confirmModal');
        const confirmBackdrop = document.getElementById('confirmBackdrop');
        const confirmCard = document.getElementById('confirmCard');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmOkBtn = document.getElementById('confirmOkBtn');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');

        function showConfirm(title, message, url, isForm = false, formId = null) {
            confirmTitle.textContent = title;
            confirmMessage.textContent = message;

            if (isForm && formId) {
                confirmOkBtn.removeAttribute('href');
                confirmOkBtn.onclick = function() {
                    document.getElementById(formId).submit();
                };
            } else {
                confirmOkBtn.onclick = null;
                confirmOkBtn.href = url;
            }

            confirmModal.classList.remove('hidden');
            // Animate in
            setTimeout(() => {
                confirmBackdrop.classList.remove('opacity-0');
                confirmCard.classList.remove('scale-95', 'opacity-0');
                confirmCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeConfirm() {
            // Animate out
            confirmBackdrop.classList.add('opacity-0');
            confirmCard.classList.remove('scale-100', 'opacity-100');
            confirmCard.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                confirmModal.classList.add('hidden');
            }, 200);
        }

        confirmCancelBtn.addEventListener('click', closeConfirm);
        confirmBackdrop.addEventListener('click', closeConfirm);

        // Hook Logout Button
        const logoutLinks = document.querySelectorAll('a[href*="logout"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                showConfirm('Keluar Aplikasi', 'Apakah Anda yakin ingin logout dari akun Anda?', this.href);
            });
        });

        // Expose to global scope for delete buttons
        window.showConfirm = showConfirm;
    </script>
</body>

</html>