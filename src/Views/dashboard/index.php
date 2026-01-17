<?php

/**
 * Dashboard - Clean Design
 */
$title = 'Dashboard';
ob_start();
?>

<div class="space-y-6 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Selamat Datang!</h1>
            <p class="text-sm text-gray-500"><?= $mahasiswa['nama'] ?? 'Mahasiswa' ?></p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">Semester <?= $mahasiswa['semester_aktif'] ?? '-' ?></p>
            <p class="text-xs text-gray-400"><?= date('l, d M Y') ?></p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-calendar text-primary-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_semester'] ?></p>
                    <p class="text-xs text-gray-500">Semester</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-book text-blue-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_matkul'] ?></p>
                    <p class="text-xs text-gray-500">Mata Kuliah</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-file text-green-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_materi'] ?></p>
                    <p class="text-xs text-gray-500">Materi</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-tasks text-amber-600"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?= $stats['total_tugas'] ?></p>
                    <p class="text-xs text-gray-500">Tugas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tugas Prioritas -->
    <div class="card bg-gradient-to-br from-red-50 to-white border-red-100">
        <div class="card-header pb-2 bg-transparent border-0 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-fire text-red-500"></i> Tugas Prioritas
                <span class="text-xs font-normal text-gray-500 bg-white px-2 py-0.5 rounded-full border border-gray-200">Deadline < 2 hari</span>
            </h2>
            <a href="<?= base_url('tugas') ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Lihat Semua</a>
        </div>

        <?php if (!empty($priorityTugas)): ?>
            <!-- Horizontal Scroll Container -->
            <div class="p-4 pt-0 overflow-x-auto pb-2 -mx-4 px-4 flex gap-4 scrollbar-hide snap-x">
                <?php foreach ($priorityTugas as $tugas):
                    $deadline = strtotime($tugas['deadline']);
                    $isOverdue = $deadline < time();

                    // Format deadline
                    if (date('Y-m-d') == date('Y-m-d', $deadline)) {
                        $deadlineStr = 'Hari ini, ' . date('H:i', $deadline);
                    } elseif (date('Y-m-d', strtotime('+1 day')) == date('Y-m-d', $deadline)) {
                        $deadlineStr = 'Besok, ' . date('H:i', $deadline);
                    } else {
                        $deadlineStr = date('d M, H:i', $deadline);
                    }
                ?>
                    <div class="min-w-[280px] w-[280px] snap-center bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                        <?php if ($isOverdue): ?>
                            <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] uppercase font-bold px-2 py-0.5 rounded-bl-lg z-10">
                                Terlewat
                            </div>
                        <?php else: ?>
                            <div class="absolute top-0 right-0 bg-amber-500 text-white text-[10px] uppercase font-bold px-2 py-0.5 rounded-bl-lg z-10">
                                Segera
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-md font-medium mb-1 truncate max-w-full">
                                <?= $tugas['mk_nama'] ?>
                            </span>
                            <h3 class="font-bold text-gray-800 leading-tight line-clamp-2 h-10" title="<?= $tugas['judul'] ?>">
                                <?= $tugas['judul'] ?>
                            </h3>
                        </div>

                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <i class="far fa-clock mr-1.5 <?= $isOverdue ? 'text-red-500' : 'text-amber-500' ?>"></i>
                            <span class="<?= $isOverdue ? 'text-red-600 font-medium' : '' ?>"><?= $deadlineStr ?></span>
                        </div>

                        <a href="<?= base_url('tugas/detail?id=' . $tugas['id']) ?>" class="block w-full text-center py-2 rounded-lg bg-gray-50 text-primary-600 text-sm font-medium hover:bg-primary-50 hover:text-primary-700 transition-colors">
                            Detail Tugas
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state py-8 text-center bg-white/50 rounded-lg mx-4 mb-4 border border-dashed border-gray-200">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="font-medium text-gray-800">Aman Terkendali!</h3>
                <p class="text-sm text-gray-500 mt-1">Tidak ada tugas mendesak untuk 2 hari ke depan.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-3">
        <a href="<?= base_url('semester/tambah') ?>" class="card p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
                    <i class="fas fa-plus text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Tambah Semester</p>
                    <p class="text-xs text-gray-500">Buat semester baru</p>
                </div>
            </div>
        </a>
        <a href="<?= base_url('mata-kuliah/tambah') ?>" class="card p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                    <i class="fas fa-book-medical text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-800">Tambah Matkul</p>
                    <p class="text-xs text-gray-500">Buat mata kuliah baru</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>