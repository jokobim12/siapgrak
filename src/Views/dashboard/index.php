<?php

/**
 * Dashboard - Clean Minimal Design (Mobile Optimized)
 */
$title = 'Dashboard';
ob_start();
?>

<div class="space-y-5 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Selamat Datang!</h1>
            <p class="text-sm text-gray-500 truncate max-w-[200px] lg:max-w-none"><?= $mahasiswa['nama'] ?? 'Mahasiswa' ?></p>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-sm font-semibold text-blue-600">Semester <?= $mahasiswa['semester_aktif'] ?? '-' ?></p>
            <p class="text-xs text-gray-400"><?= date('d M Y') ?></p>
        </div>
    </div>

    <!-- Quick Stats - Compact Mobile Grid -->
    <div class="grid grid-cols-4 gap-2 lg:gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-3 lg:p-4 text-center">
            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-blue-600 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-calendar text-white text-xs lg:text-sm"></i>
            </div>
            <p class="text-xl lg:text-2xl font-bold text-gray-900"><?= $stats['total_semester'] ?></p>
            <p class="text-[10px] lg:text-xs text-gray-500">Semester</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 lg:p-4 text-center">
            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-blue-600 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-book text-white text-xs lg:text-sm"></i>
            </div>
            <p class="text-xl lg:text-2xl font-bold text-gray-900"><?= $stats['total_matkul'] ?></p>
            <p class="text-[10px] lg:text-xs text-gray-500">Matkul</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 lg:p-4 text-center">
            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-blue-600 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-file-alt text-white text-xs lg:text-sm"></i>
            </div>
            <p class="text-xl lg:text-2xl font-bold text-gray-900"><?= $stats['total_materi'] ?></p>
            <p class="text-[10px] lg:text-xs text-gray-500">Materi</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 lg:p-4 text-center">
            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg bg-blue-600 flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-tasks text-white text-xs lg:text-sm"></i>
            </div>
            <p class="text-xl lg:text-2xl font-bold text-gray-900"><?= $stats['total_tugas'] ?></p>
            <p class="text-[10px] lg:text-xs text-gray-500">Tugas</p>
        </div>
    </div>

    <!-- Tugas Prioritas -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-900 text-sm lg:text-base">Tugas Prioritas</h2>
            <a href="<?= base_url('tugas') ?>" class="text-xs lg:text-sm text-blue-600 hover:text-blue-700 font-medium">
                Lihat Semua
            </a>
        </div>

        <?php if (!empty($priorityTugas)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($priorityTugas as $tugas):
                    $deadline = strtotime($tugas['deadline']);
                    $isOverdue = $deadline < time();

                    // Format deadline
                    if (date('Y-m-d') == date('Y-m-d', $deadline)) {
                        $deadlineStr = 'Hari ini ' . date('H:i', $deadline);
                    } elseif (date('Y-m-d', strtotime('+1 day')) == date('Y-m-d', $deadline)) {
                        $deadlineStr = 'Besok ' . date('H:i', $deadline);
                    } else {
                        $deadlineStr = date('d M H:i', $deadline);
                    }
                ?>
                    <a href="<?= base_url('tugas/detail?id=' . $tugas['id']) ?>"
                        class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg <?= $isOverdue ? 'bg-red-100' : 'bg-amber-100' ?> flex items-center justify-center flex-shrink-0">
                            <i class="fas <?= $isOverdue ? 'fa-exclamation text-red-600' : 'fa-clock text-amber-600' ?> text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 text-sm truncate"><?= $tugas['judul'] ?></h3>
                            <p class="text-xs text-gray-500 truncate"><?= $tugas['mk_nama'] ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-medium <?= $isOverdue ? 'text-red-600' : 'text-gray-700' ?>"><?= $deadlineStr ?></p>
                            <?php if ($isOverdue): ?>
                                <span class="text-[10px] text-red-500 font-medium">Terlewat!</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-10 text-center">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check text-green-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Aman Terkendali!</h3>
                <p class="text-sm text-gray-500">Tidak ada tugas mendesak</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions - Smaller on Mobile -->
    <div class="grid grid-cols-2 gap-3">
        <a href="<?= base_url('semester/tambah') ?>" class="flex items-center gap-3 p-3 lg:p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-plus text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-sm truncate">Tambah Semester</p>
                <p class="text-xs text-gray-500 hidden lg:block">Buat semester baru</p>
            </div>
        </a>
        <a href="<?= base_url('mata-kuliah/tambah') ?>" class="flex items-center gap-3 p-3 lg:p-4 bg-white border border-gray-200 rounded-xl hover:border-blue-300 transition-all">
            <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-book-medical text-white"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-sm truncate">Tambah Matkul</p>
                <p class="text-xs text-gray-500 hidden lg:block">Buat mata kuliah baru</p>
            </div>
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>