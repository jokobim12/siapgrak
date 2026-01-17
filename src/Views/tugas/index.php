<?php

/**
 * Tugas Index View
 */
$title = 'Tugas';
$pageTitle = 'Daftar Tugas';
ob_start();
?>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="?filter=all" class="card p-4 <?= $filter === 'all' ? 'ring-2 ring-primary-500' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-list text-gray-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900"><?= $stats['total'] ?></p>
                    <p class="text-xs text-gray-500">Semua Tugas</p>
                </div>
            </div>
        </a>

        <a href="?filter=pending" class="card p-4 <?= $filter === 'pending' ? 'ring-2 ring-amber-500' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900"><?= $stats['pending'] ?></p>
                    <p class="text-xs text-gray-500">Belum Dikumpulkan</p>
                </div>
            </div>
        </a>

        <a href="?filter=submitted" class="card p-4 <?= $filter === 'submitted' ? 'ring-2 ring-emerald-500' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900"><?= $stats['submitted'] ?></p>
                    <p class="text-xs text-gray-500">Sudah Dikumpulkan</p>
                </div>
            </div>
        </a>

        <a href="?filter=late" class="card p-4 <?= $filter === 'late' ? 'ring-2 ring-red-500' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900"><?= $stats['late'] ?></p>
                    <p class="text-xs text-gray-500">Terlambat</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Tugas List -->
    <div class="card">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">
                <?php
                $filterLabels = [
                    'all' => 'Semua Tugas',
                    'pending' => 'Tugas Belum Dikumpulkan',
                    'submitted' => 'Tugas Sudah Dikumpulkan',
                    'late' => 'Tugas Terlambat'
                ];
                echo $filterLabels[$filter] ?? 'Semua Tugas';
                ?>
            </h2>
        </div>

        <?php if (!empty($tugasList)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($tugasList as $tugas): ?>
                    <a href="<?= base_url('tugas/detail?id=' . $tugas['id']) ?>"
                        class="block p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Status Icon -->
                            <div class="flex-shrink-0">
                                <?php if ($tugas['submission_id']): ?>
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                        <i class="fas fa-check text-emerald-600"></i>
                                    </div>
                                <?php elseif ($tugas['sisa_hari'] < 0): ?>
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <i class="fas fa-times text-red-600"></i>
                                    </div>
                                <?php elseif ($tugas['sisa_hari'] <= 1): ?>
                                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                                        <i class="fas fa-exclamation text-amber-600"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <i class="fas fa-tasks text-gray-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-medium text-gray-900"><?= $tugas['judul'] ?></h3>
                                    <?php if ($tugas['submission_id']): ?>
                                        <span class="badge-success">Sudah Dikumpulkan</span>
                                        <?php if ($tugas['nilai']): ?>
                                            <span class="badge-primary">Nilai: <?= $tugas['nilai'] ?></span>
                                        <?php endif; ?>
                                    <?php elseif ($tugas['sisa_hari'] < 0): ?>
                                        <span class="badge-danger">Terlambat <?= abs($tugas['sisa_hari']) ?> hari</span>
                                    <?php elseif ($tugas['sisa_hari'] <= 1): ?>
                                        <span class="badge-warning">Deadline Segera!</span>
                                    <?php elseif ($tugas['sisa_hari'] <= 3): ?>
                                        <span class="badge bg-blue-100 text-blue-800"><?= $tugas['sisa_hari'] ?> hari lagi</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    <?= $tugas['nama_mk'] ?> (<?= $tugas['kode_mk'] ?>) - Pertemuan <?= $tugas['nomor_pertemuan'] ?>
                                </p>
                                <?php if ($tugas['deskripsi']): ?>
                                    <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?= $tugas['deskripsi'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Deadline -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-medium <?= $tugas['sisa_hari'] < 0 ? 'text-red-600' : 'text-gray-900' ?>">
                                    <?= formatDate($tugas['deadline']) ?>
                                </p>
                                <p class="text-xs text-gray-500"><?= date('H:i', strtotime($tugas['deadline'])) ?> WITA</p>
                                <?php if ($tugas['submission_id'] && $tugas['submitted_at']): ?>
                                    <p class="text-xs text-emerald-600 mt-1">
                                        Dikumpulkan: <?= formatDate($tugas['submitted_at']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-clipboard-check text-5xl mb-4 text-gray-300"></i>
                <p>Tidak ada tugas</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>