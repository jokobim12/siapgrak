<?php

/**
 * Detail Mata Kuliah dengan Pertemuan
 */
$title = $mataKuliah['nama_mk'];
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('mata-kuliah') ?>" class="p-2 -ml-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-bold text-gray-800 truncate"><?= $mataKuliah['nama_mk'] ?></h1>
            <p class="text-sm text-gray-500"><?= $mataKuliah['semester_nama'] ?></p>
        </div>
        <a href="<?= base_url('mata-kuliah/hapus?id=' . $mataKuliah['id']) ?>"
            class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
            onclick="return confirm('Hapus mata kuliah ini? Semua materi akan ikut terhapus.')">
            <i class="fas fa-trash"></i>
        </a>
    </div>

    <!-- Grid Pertemuan -->
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
        <?php foreach ($pertemuanList as $p): ?>
            <a href="<?= base_url('pertemuan?id=' . $p['id']) ?>"
                class="card p-4 text-center hover:bg-gray-50 transition-colors <?= $p['total_materi'] > 0 ? 'border-primary-200 bg-primary-50' : '' ?>">
                <span class="text-lg font-bold <?= $p['total_materi'] > 0 ? 'text-primary-600' : 'text-gray-400' ?>">
                    P<?= $p['nomor_pertemuan'] ?>
                </span>
                <?php if ($p['total_materi'] > 0): ?>
                    <p class="text-xs text-primary-600 mt-1"><?= $p['total_materi'] ?> materi</p>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Summary -->
    <div class="card p-4">
        <div class="grid grid-cols-2 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-primary-600">
                    <?= array_sum(array_column($pertemuanList, 'total_materi')) ?>
                </p>
                <p class="text-sm text-gray-500">Total Materi</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-amber-600">
                    <?= array_sum(array_column($pertemuanList, 'total_tugas')) ?>
                </p>
                <p class="text-sm text-gray-500">Total Tugas</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>