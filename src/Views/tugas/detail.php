<?php

/**
 * Detail Tugas
 */
$title = $tugas['judul'];
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('tugas') ?>" class="p-2 -ml-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-bold text-gray-800 truncate"><?= $tugas['judul'] ?></h1>
            <p class="text-sm text-gray-500"><?= $tugas['nama_mk'] ?> · P<?= $tugas['nomor_pertemuan'] ?></p>
        </div>
    </div>

    <!-- Status Banner -->
    <?php if ($submission): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle flex-shrink-0"></i>
            <div>
                <p class="font-medium">Tugas sudah dikumpulkan</p>
                <p class="text-sm">Dikumpulkan pada <?= formatDateTime($submission['submitted_at']) ?></p>
            </div>
        </div>
    <?php elseif ($tugas['sisa_hari'] < 0): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle flex-shrink-0"></i>
            <div>
                <p class="font-medium">Tugas terlambat</p>
                <p class="text-sm">Deadline sudah lewat <?= abs($tugas['sisa_hari']) ?> hari</p>
            </div>
        </div>
    <?php elseif ($tugas['sisa_hari'] <= 3): ?>
        <div class="alert-warning">
            <i class="fas fa-clock flex-shrink-0"></i>
            <div>
                <p class="font-medium">Deadline mendekat</p>
                <p class="text-sm"><?= $tugas['sisa_hari'] == 0 ? 'Hari ini!' : $tugas['sisa_hari'] . ' hari lagi' ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="card">
        <div class="p-4 space-y-4">
            <div>
                <p class="text-sm text-gray-500">Deadline</p>
                <p class="font-medium text-gray-800"><?= formatDateTime($tugas['deadline']) ?></p>
            </div>
            <?php if ($tugas['deskripsi']): ?>
                <div>
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="text-gray-800"><?= nl2br($tugas['deskripsi']) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Submission -->
    <?php if ($submission): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-800">File Pengumpulan</h2>
            </div>
            <div class="p-4">
                <a href="<?= $submission['file_gdrive_url'] ?>" target="_blank" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-file text-primary-600"></i>
                    <span class="flex-1 truncate"><?= $submission['nama_file'] ?></span>
                    <i class="fas fa-external-link-alt text-gray-400"></i>
                </a>
                <?php if ($submission['catatan']): ?>
                    <p class="mt-3 text-sm text-gray-600"><?= $submission['catatan'] ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Submit Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-800">Kumpulkan Tugas</h2>
            </div>
            <form method="POST" action="<?= base_url('tugas/submit') ?>" enctype="multipart/form-data" class="p-4 space-y-4">
                <input type="hidden" name="tugas_id" value="<?= $tugas['id'] ?>">
                <div>
                    <label class="label">File</label>
                    <input type="file" name="file" class="input" required>
                </div>
                <div>
                    <label class="label">Catatan (Opsional)</label>
                    <textarea name="catatan" class="input" rows="2" placeholder="Tambahkan catatan jika ada..."></textarea>
                </div>
                <button type="submit" class="btn-primary w-full">
                    <i class="fas fa-upload mr-2"></i>Kumpulkan
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>