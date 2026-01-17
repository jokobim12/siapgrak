<?php

/**
 * Admin Layout
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - SIAPGRAK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white fixed h-full">
            <div class="p-4 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg gradient-primary flex items-center justify-center">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Admin Panel</p>
                        <p class="text-xs text-gray-400">SIAPGRAK</p>
                    </div>
                </div>
            </div>

            <nav class="p-4 space-y-1">
                <a href="<?= base_url('admin/dashboard') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= base_url('admin/semester') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'semester') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-calendar w-5"></i>
                    <span>Semester</span>
                </a>
                <a href="<?= base_url('admin/kelas') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'kelas') && !str_contains($_SERVER['REQUEST_URI'], 'mahasiswa') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>Kelas</span>
                </a>
                <a href="<?= base_url('admin/mata-kuliah') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'mata-kuliah') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-book w-5"></i>
                    <span>Mata Kuliah</span>
                </a>
                <a href="<?= base_url('admin/jadwal') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'jadwal') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-clock w-5"></i>
                    <span>Jadwal</span>
                </a>
                <a href="<?= base_url('admin/kelas-mahasiswa') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'kelas-mahasiswa') ? 'bg-gray-800' : '' ?>">
                    <i class="fas fa-user-plus w-5"></i>
                    <span>Assign Mahasiswa</span>
                </a>
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
                <a href="<?= base_url('admin/logout') ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-600/20 text-red-400 transition-colors">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 ml-64">
            <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-semibold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h1>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-user mr-1"></i>
                            <?= $_SESSION['admin']['nama'] ?? 'Admin' ?>
                        </span>
                    </div>
                </div>
            </header>

            <?php if ($success = flash('success')): ?>
                <div class="notification-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error = flash('error')): ?>
                <div class="notification-error"><?= $error ?></div>
            <?php endif; ?>

            <main class="p-6">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script>
        setTimeout(() => {
            document.querySelectorAll('.notification-success, .notification-error').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
</body>

</html>