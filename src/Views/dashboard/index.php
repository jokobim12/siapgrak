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

    <!-- Recent Mata Kuliah -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Mata Kuliah Terbaru</h2>
            <a href="<?= base_url('mata-kuliah') ?>" class="text-sm text-primary-600">Lihat Semua</a>
        </div>
        <?php if (!empty($recentMatkul)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($recentMatkul as $mk): ?>
                    <a href="<?= base_url('mata-kuliah/detail?id=' . $mk['id']) ?>" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-book text-primary-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate"><?= $mk['nama_mk'] ?></p>
                            <p class="text-sm text-gray-500"><?= $mk['semester_nama'] ?></p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state py-8">
                <i class="fas fa-book-open empty-state-icon"></i>
                <p class="text-gray-500">Belum ada mata kuliah</p>
                <a href="<?= base_url('mata-kuliah/tambah') ?>" class="btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i>Tambah Mata Kuliah
                </a>
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