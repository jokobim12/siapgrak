<?php

/**
 * Tugas Detail View
 */
$title = $tugas['judul'];
$pageTitle = 'Detail Tugas';
ob_start();
?>

<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500">
        <a href="<?= base_url('tugas') ?>" class="hover:text-primary-600">Tugas</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900"><?= $tugas['judul'] ?></span>
    </nav>

    <!-- Status Banner -->
    <?php if ($submission): ?>
        <div class="card p-4 bg-emerald-50 border-emerald-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-check text-emerald-600"></i>
                </div>
                <div>
                    <p class="font-medium text-emerald-800">Tugas Sudah Dikumpulkan</p>
                    <p class="text-sm text-emerald-600">Dikumpulkan pada <?= formatDateTime($submission['submitted_at']) ?></p>
                </div>
                <?php if ($submission['nilai']): ?>
                    <div class="ml-auto text-right">
                        <p class="text-2xl font-bold text-emerald-600"><?= $submission['nilai'] ?></p>
                        <p class="text-xs text-emerald-600">Nilai</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif ($tugas['sisa_hari'] < 0): ?>
        <div class="card p-4 bg-red-50 border-red-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <p class="font-medium text-red-800">Deadline Terlewat!</p>
                    <p class="text-sm text-red-600">Terlambat <?= abs($tugas['sisa_hari']) ?> hari</p>
                </div>
            </div>
        </div>
    <?php elseif ($tugas['sisa_hari'] <= 1): ?>
        <div class="card p-4 bg-amber-50 border-amber-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600"></i>
                </div>
                <div>
                    <p class="font-medium text-amber-800">Deadline Segera!</p>
                    <p class="text-sm text-amber-600">Tersisa <?= $tugas['sisa_hari'] ?> hari lagi</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tugas Card -->
    <div class="card">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-start justify-between">
                <div>
                    <span class="badge-primary"><?= $tugas['kode_mk'] ?></span>
                    <h1 class="text-2xl font-bold text-gray-900 mt-2"><?= $tugas['judul'] ?></h1>
                    <p class="text-gray-500 mt-1">
                        <?= $tugas['nama_mk'] ?> - Pertemuan <?= $tugas['nomor_pertemuan'] ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <!-- Info Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Deadline</p>
                    <p class="font-semibold text-gray-900"><?= formatDateTime($tugas['deadline']) ?></p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Dosen</p>
                    <p class="font-semibold text-gray-900"><?= $tugas['dosen'] ?: '-' ?></p>
                </div>
            </div>

            <!-- Description -->
            <?php if ($tugas['deskripsi']): ?>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Deskripsi Tugas</h3>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-gray-600 whitespace-pre-line"><?= $tugas['deskripsi'] ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Attached File -->
            <?php if ($tugas['file_gdrive_id']): ?>
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">File Lampiran</h3>
                    <a href="<?= $tugas['file_gdrive_url'] ?>" target="_blank"
                        class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                            <i class="fas fa-file text-gray-500"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900"><?= $tugas['nama_file'] ?: 'File Tugas' ?></p>
                            <p class="text-sm text-gray-500">Klik untuk membuka</p>
                        </div>
                        <i class="fas fa-external-link-alt text-gray-400"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Submission Section -->
    <?php if ($submission): ?>
        <div class="card">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">
                    <i class="fas fa-paper-plane text-emerald-500 mr-2"></i>
                    Pengumpulan Anda
                </h3>
            </div>
            <div class="p-6">
                <a href="<?= $submission['file_gdrive_url'] ?>" target="_blank"
                    class="flex items-center gap-3 p-4 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-white border border-emerald-200 flex items-center justify-center">
                        <i class="fas fa-file-alt text-emerald-500"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900"><?= $submission['nama_file'] ?></p>
                        <p class="text-sm text-gray-500">Dikumpulkan: <?= formatDateTime($submission['submitted_at']) ?></p>
                    </div>
                    <i class="fas fa-external-link-alt text-gray-400"></i>
                </a>

                <?php if ($submission['catatan']): ?>
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700">Catatan Anda:</p>
                        <p class="text-gray-600 mt-1"><?= $submission['catatan'] ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($submission['feedback']): ?>
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-blue-800">Feedback Dosen:</p>
                        <p class="text-blue-700 mt-1"><?= $submission['feedback'] ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Submit Form -->
        <div class="card">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">
                    <i class="fas fa-upload text-primary-500 mr-2"></i>
                    Kumpulkan Tugas
                </h3>
            </div>
            <div class="p-6">
                <form action="<?= base_url('tugas/submit') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tugas_id" value="<?= $tugas['id'] ?>">

                    <div class="space-y-4">
                        <div>
                            <label class="label">File Tugas</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary-400 transition-colors cursor-pointer"
                                onclick="document.getElementById('submitFile').click()">
                                <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600">Klik untuk memilih file</p>
                                <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, PowerPoint, Gambar, atau ZIP</p>
                                <p id="selectedFile" class="text-sm text-primary-600 font-medium mt-3 hidden"></p>
                            </div>
                            <input type="file" name="file" id="submitFile" class="hidden" required
                                onchange="document.getElementById('selectedFile').textContent = this.files[0]?.name; document.getElementById('selectedFile').classList.remove('hidden')">
                        </div>

                        <div>
                            <label class="label">Catatan (Opsional)</label>
                            <textarea name="catatan" class="input" rows="3" placeholder="Tambahkan catatan untuk dosen..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary w-full mt-6">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kumpulkan Tugas
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>