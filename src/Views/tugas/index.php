<?php

/**
 * Daftar Tugas
 */
$title = 'Tugas';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Tugas</h1>
    </div>

    <!-- Filter Stats -->
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
        <a href="<?= base_url('tugas') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-white border border-gray-300 text-gray-700' ?>">
            Semua (<?= $stats['total'] ?>)
        </a>
        <a href="<?= base_url('tugas?filter=pending') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'pending' ? 'bg-amber-500 text-white' : 'bg-white border border-gray-300 text-gray-700' ?>">
            Pending (<?= $stats['pending'] ?>)
        </a>
        <a href="<?= base_url('tugas?filter=submitted') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'submitted' ? 'bg-green-500 text-white' : 'bg-white border border-gray-300 text-gray-700' ?>">
            Submitted (<?= $stats['submitted'] ?>)
        </a>
        <a href="<?= base_url('tugas?filter=late') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'late' ? 'bg-red-500 text-white' : 'bg-white border border-gray-300 text-gray-700' ?>">
            Terlambat (<?= $stats['late'] ?>)
        </a>
    </div>

    <!-- Tugas List -->
    <?php if (!empty($tugasList)): ?>
        <div class="space-y-3">
            <?php foreach ($tugasList as $t): ?>
                <a href="<?= base_url('tugas/detail?id=' . $t['id']) ?>" class="card block">
                    <div class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 <?php
                                                                                                            if ($t['submission_id']) {
                                                                                                                echo 'bg-green-100';
                                                                                                            } elseif ($t['sisa_hari'] < 0) {
                                                                                                                echo 'bg-red-100';
                                                                                                            } elseif ($t['sisa_hari'] <= 3) {
                                                                                                                echo 'bg-amber-100';
                                                                                                            } else {
                                                                                                                echo 'bg-blue-100';
                                                                                                            }
                                                                                                            ?>">
                                <i class="fas <?php
                                                if ($t['submission_id']) {
                                                    echo 'fa-check text-green-600';
                                                } elseif ($t['sisa_hari'] < 0) {
                                                    echo 'fa-exclamation text-red-600';
                                                } else {
                                                    echo 'fa-clipboard-list text-blue-600';
                                                }
                                                ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-800 truncate"><?= $t['judul'] ?></h3>
                                <p class="text-sm text-gray-500"><?= $t['nama_mk'] ?> · P<?= $t['nomor_pertemuan'] ?></p>
                                <p class="text-xs mt-1 <?php
                                                        if ($t['sisa_hari'] < 0) echo 'text-red-600';
                                                        elseif ($t['sisa_hari'] <= 3) echo 'text-amber-600';
                                                        else echo 'text-gray-500';
                                                        ?>">
                                    <?php if ($t['submission_id']): ?>
                                        <i class="fas fa-check mr-1"></i>Dikumpulkan
                                    <?php elseif ($t['sisa_hari'] < 0): ?>
                                        <i class="fas fa-exclamation mr-1"></i>Terlambat <?= abs($t['sisa_hari']) ?> hari
                                    <?php elseif ($t['sisa_hari'] == 0): ?>
                                        <i class="fas fa-clock mr-1"></i>Hari ini!
                                    <?php else: ?>
                                        <i class="fas fa-clock mr-1"></i><?= $t['sisa_hari'] ?> hari lagi
                                    <?php endif; ?>
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-clipboard-check empty-state-icon"></i>
                <p class="font-medium text-gray-600">Tidak ada tugas</p>
                <p class="text-sm text-gray-500 mt-1">Tambahkan tugas dari halaman pertemuan</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>