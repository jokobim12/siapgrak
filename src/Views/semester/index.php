<?php

/**
 * Daftar Semester
 */
$title = 'Semester';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Semester</h1>
        <a href="<?= base_url('semester/tambah') ?>" class="btn-primary btn-sm">
            <i class="fas fa-plus mr-2"></i>Tambah
        </a>
    </div>

    <?php if (!empty($semesters)): ?>
        <div class="space-y-3">
            <?php foreach ($semesters as $s): ?>
                <div class="card">
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <i class="fas fa-calendar text-primary-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= $s['nama'] ?></h3>
                                    <p class="text-sm text-gray-500"><?= $s['total_matkul'] ?> mata kuliah · <?= $s['total_materi'] ?> materi</p>
                                </div>
                            </div>
                            <?php if ($s['is_active']): ?>
                                <span class="badge-success">Aktif</span>
                            <?php else: ?>
                                <a href="<?= base_url('semester/set-aktif?id=' . $s['id']) ?>" class="badge-gray hover:bg-gray-200">Set Aktif</a>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                            <a href="<?= base_url('mata-kuliah?semester_id=' . $s['id']) ?>" class="text-sm text-primary-600">
                                Lihat Mata Kuliah <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                            <a href="#" onclick="showConfirm('Hapus Semester', 'Apakah Anda yakin ingin menghapus semester ini? Semua Mata Kuliah dan Materi di dalamnya akan ikut terhapus permanen.', '<?= base_url('semester/hapus?id=' . $s['id']) ?>')" class="text-sm text-red-600">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-calendar-plus empty-state-icon"></i>
                <p class="font-medium text-gray-600">Belum ada semester</p>
                <p class="text-sm text-gray-500 mt-1">Tambahkan semester pertama Anda</p>
                <a href="<?= base_url('semester/tambah') ?>" class="btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i>Tambah Semester
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>