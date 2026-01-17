<?php

/**
 * Daftar Mata Kuliah - Grid Layout
 */
$title = 'Mata Kuliah';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header with Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Mata Kuliah</h1>
            <?php if (!empty($semesters)): ?>
                <select onchange="window.location.href=this.value"
                    class="text-sm border-gray-200 rounded-lg py-1.5 pl-3 pr-8 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                    <option value="<?= base_url('mata-kuliah') ?>" <?= !$selectedSemester ? 'selected' : '' ?>>Semua Semester</option>
                    <?php foreach ($semesters as $s): ?>
                        <option value="<?= base_url('mata-kuliah?semester_id=' . $s['id']) ?>" <?= $selectedSemester == $s['id'] ? 'selected' : '' ?>>
                            <?= $s['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('mata-kuliah/tambah') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Tambah</span>
        </a>
    </div>

    <!-- Grid Matkul -->
    <?php if (!empty($mataKuliahList)): ?>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <?php foreach ($mataKuliahList as $mk): ?>
                <a href="<?= base_url('mata-kuliah/detail?id=' . $mk['id']) ?>"
                    class="bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all group">
                    <!-- Icon & Materi Badge -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                            <i class="fas fa-book text-white text-sm"></i>
                        </div>
                        <span class="text-xs font-medium <?= $mk['total_materi'] > 0 ? 'text-blue-600 bg-blue-50' : 'text-gray-400 bg-gray-100' ?> px-2 py-0.5 rounded-full">
                            <?= $mk['total_materi'] ?> materi
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="font-semibold text-gray-900 text-sm leading-tight mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                        <?= $mk['nama_mk'] ?>
                    </h3>

                    <!-- Semester -->
                    <p class="text-xs text-gray-500"><?= $mk['semester_nama'] ?></p>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Count Info -->
        <p class="text-xs text-gray-400 text-center">
            Menampilkan <?= count($mataKuliahList) ?> mata kuliah
        </p>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book-open text-gray-400 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Belum ada mata kuliah</h3>
            <p class="text-sm text-gray-500 mb-6">Tambahkan mata kuliah pertama Anda</p>
            <a href="<?= base_url('mata-kuliah/tambah') ?>" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus"></i>
                Tambah Mata Kuliah
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>