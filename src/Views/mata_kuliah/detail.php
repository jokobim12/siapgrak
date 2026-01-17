<?php

/**
 * Detail Mata Kuliah dengan Pertemuan - Clean Design
 */
$title = $mataKuliah['nama_mk'];
$totalMateri = array_sum(array_column($pertemuanList, 'total_materi'));
$totalTugas = array_sum(array_column($pertemuanList, 'total_tugas'));
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= base_url('mata-kuliah') ?>" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg lg:text-xl font-bold text-gray-900 truncate"><?= $mataKuliah['nama_mk'] ?></h1>
                <p class="text-xs text-gray-500"><?= $mataKuliah['semester_nama'] ?></p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <!-- Stats Badge -->
            <div class="hidden sm:flex items-center gap-3 text-xs">
                <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg font-medium">
                    <i class="fas fa-file-alt mr-1"></i><?= $totalMateri ?> materi
                </span>
                <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded-lg font-medium">
                    <i class="fas fa-tasks mr-1"></i><?= $totalTugas ?> tugas
                </span>
            </div>
            <button onclick="showConfirm('Hapus Mata Kuliah', 'Apakah Anda yakin ingin menghapus mata kuliah ini? Semua materi dan tugas akan ikut terhapus.', '<?= base_url('mata-kuliah/hapus?id=' . $mataKuliah['id']) ?>')"
                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <i class="fas fa-trash text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Stats -->
    <div class="sm:hidden flex items-center gap-3 text-xs">
        <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg font-medium">
            <i class="fas fa-file-alt mr-1"></i><?= $totalMateri ?> materi
        </span>
        <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded-lg font-medium">
            <i class="fas fa-tasks mr-1"></i><?= $totalTugas ?> tugas
        </span>
    </div>

    <!-- Pertemuan Grid - Compact -->
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Pilih Pertemuan</h2>
        <div class="grid grid-cols-6 sm:grid-cols-9 lg:grid-cols-18 gap-2">
            <?php foreach ($pertemuanList as $p):
                $hasContent = $p['total_materi'] > 0 || $p['total_tugas'] > 0;
            ?>
                <a href="<?= base_url('pertemuan?id=' . $p['id']) ?>"
                    class="aspect-square flex flex-col items-center justify-center rounded-lg text-sm font-semibold transition-all
                          <?= $hasContent
                                ? 'bg-blue-600 text-white hover:bg-blue-700'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                    <?= $p['nomor_pertemuan'] ?>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="text-[10px] text-gray-400 text-center mt-3">
            <span class="inline-block w-3 h-3 bg-blue-600 rounded mr-1"></span>Ada konten
            <span class="inline-block w-3 h-3 bg-gray-100 rounded ml-3 mr-1"></span>Kosong
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>