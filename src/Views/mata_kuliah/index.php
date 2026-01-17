<?php

/**
 * Daftar Mata Kuliah
 */
$title = 'Mata Kuliah';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Mata Kuliah</h1>
        <a href="<?= base_url('mata-kuliah/tambah') ?>" class="btn-primary btn-sm">
            <i class="fas fa-plus mr-2"></i>Tambah
        </a>
    </div>

    <!-- Filter Semester -->
    <?php if (!empty($semesters)): ?>
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
            <a href="<?= base_url('mata-kuliah') ?>"
                class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= !$selectedSemester ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                Semua
            </a>
            <?php foreach ($semesters as $s): ?>
                <a href="<?= base_url('mata-kuliah?semester_id=' . $s['id']) ?>"
                    class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $selectedSemester == $s['id'] ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                    <?= $s['nama'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Lista Matkul -->
    <?php if (!empty($mataKuliahList)): ?>
        <div class="space-y-3">
            <?php foreach ($mataKuliahList as $mk): ?>
                <a href="<?= base_url('mata-kuliah/detail?id=' . $mk['id']) ?>" class="card block">
                    <div class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-book text-blue-600 text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 truncate"><?= $mk['nama_mk'] ?></h3>
                                <p class="text-sm text-gray-500"><?= $mk['semester_nama'] ?></p>
                            </div>
                            <div class="text-right">
                                <span class="badge-primary"><?= $mk['total_materi'] ?> materi</span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-book-open empty-state-icon"></i>
                <p class="font-medium text-gray-600">Belum ada mata kuliah</p>
                <p class="text-sm text-gray-500 mt-1">Tambahkan mata kuliah pertama Anda</p>
                <a href="<?= base_url('mata-kuliah/tambah') ?>" class="btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i>Tambah Mata Kuliah
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>