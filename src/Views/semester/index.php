<?php

/**
 * Daftar Semester - Clean Design
 */
$title = 'Semester';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Semester</h1>
        <a href="<?= base_url('semester/tambah') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Tambah</span>
        </a>
    </div>

    <?php if (!empty($semesters)): ?>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="divide-y divide-gray-100">
                <?php foreach ($semesters as $index => $s): ?>
                    <div class="p-4 hover:bg-gray-50 transition-colors <?= $s['is_active'] ? 'bg-blue-50/50' : '' ?>">
                        <div class="flex items-center justify-between gap-4">
                            <!-- Left: Info -->
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-12 h-12 rounded-xl <?= $s['is_active'] ? 'bg-blue-600' : 'bg-gray-100' ?> flex items-center justify-center flex-shrink-0">
                                    <?php
                                    preg_match('/(\d+)/', $s['nama'], $numMatch);
                                    $semNum = $numMatch[1] ?? ($index + 1);
                                    ?>
                                    <span class="text-lg font-bold <?= $s['is_active'] ? 'text-white' : 'text-gray-600' ?>"><?= $semNum ?></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900"><?= $s['nama'] ?></h3>
                                        <?php if ($s['is_active']): ?>
                                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-600 text-white rounded-full">Aktif</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500"><?= $s['total_matkul'] ?> mata kuliah · <?= $s['total_materi'] ?> materi</p>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <?php if (!$s['is_active']): ?>
                                    <a href="<?= base_url('semester/set-aktif?id=' . $s['id']) ?>"
                                        class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                        Set Aktif
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('mata-kuliah?semester_id=' . $s['id']) ?>"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-book mr-1"></i>
                                    <span class="hidden sm:inline">Lihat </span>Matkul
                                </a>
                                <button onclick="showConfirm('Hapus Semester', 'Apakah Anda yakin ingin menghapus semester ini? Semua Mata Kuliah dan Materi di dalamnya akan ikut terhapus permanen.', '<?= base_url('semester/hapus?id=' . $s['id']) ?>')"
                                    class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sort Info -->
        <p class="text-xs text-gray-400 text-center">
            <i class="fas fa-sort-numeric-up mr-1"></i>
            Diurutkan dari Semester 1 sampai yang terbaru
        </p>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-plus text-gray-400 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Belum ada semester</h3>
            <p class="text-sm text-gray-500 mb-6">Tambahkan semester pertama Anda untuk mulai mengelola mata kuliah</p>
            <a href="<?= base_url('semester/tambah') ?>" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus"></i>
                Tambah Semester
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>