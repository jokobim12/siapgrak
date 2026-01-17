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

    <!-- Detail Content -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="flex flex-col md:flex-row gap-8 items-start justify-between">

                <!-- Main Info -->
                <div class="flex-1 space-y-6 w-full">
                    <!-- Status Badge -->
                    <div class="flex items-center gap-3">
                        <?php if ($submission): ?>
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-green-50 text-green-700 border border-green-200">
                                <i class="fas fa-check-circle mr-2"></i>Selesai
                            </span>
                            <span class="text-sm text-gray-400">Tuntas pada <?= date('d M Y, H:i', strtotime($submission['submitted_at'])) ?></span>
                        <?php else: ?>
                            <?php if ($tugas['sisa_hari'] < 0): ?>
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <i class="fas fa-exclamation-circle mr-2"></i>Terlambat <?= abs($tugas['sisa_hari']) ?> hari
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                    <i class="fas fa-clock mr-2"></i>Belum Selesai
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Deadline Block -->
                    <div class="flex items-start gap-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100">
                        <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center text-primary-600 shadow-sm shrink-0">
                            <i class="far fa-calendar-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-primary-600 uppercase tracking-wide mb-1">Deadline</p>
                            <p class="text-gray-900 font-bold text-lg"><?= formatDateTime($tugas['deadline']) ?></p>
                            <?php if (!$submission && $tugas['sisa_hari'] >= 0): ?>
                                <p class="text-sm text-gray-500 mt-1">Sisa waktu: <?= $tugas['sisa_hari'] == 0 ? 'Hari ini' : $tugas['sisa_hari'] . ' hari lagi' ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if ($tugas['deskripsi']): ?>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Detail Tugas</h3>
                            <div class="prose prose-sm max-w-none text-gray-600 bg-gray-50 rounded-xl p-5 border border-gray-100 leading-relaxed">
                                <?= nl2br($tugas['deskripsi']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar / Actions -->
                <div class="w-full md:w-80 shrink-0 flex flex-col gap-4 sticky top-6">
                    <form action="<?= base_url($submission ? 'tugas/uncheck' : 'tugas/submit') ?>" method="POST">
                        <input type="hidden" name="tugas_id" value="<?= $tugas['id'] ?>">
                        <button type="submit" class="w-full py-4 px-6 rounded-xl font-bold text-white shadow-lg transition-transform hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-3
                            <?= $submission ? 'bg-gray-500 hover:bg-gray-600 shadow-gray-500/30' : 'bg-primary-600 hover:bg-primary-700 shadow-primary-600/30' ?>">
                            <?php if ($submission): ?>
                                <i class="fas fa-undo"></i> Batalkan Selesai
                            <?php else: ?>
                                <i class="fas fa-check-double text-xl"></i> Tandai Selesai
                            <?php endif; ?>
                        </button>
                    </form>

                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="document.getElementById('editModal').classList.remove('hidden')"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors">
                            <i class="fas fa-edit"></i> Edit
                        </button>

                        <form action="<?= base_url('tugas/delete') ?>" method="POST" onsubmit="return confirm('Hapus tugas ini permanen?');" class="block w-full">
                            <input type="hidden" name="id" value="<?= $tugas['id'] ?>">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Custom Style for Modal (Reusing safe CSS) -->
<style>
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 40;
    }

    .modal-content {
        max-width: 480px;
        width: 90%;
        margin: auto;
        background: white;
        border-radius: 12px;
        position: relative;
        z-index: 50;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-action-primary {
        background: #2563eb;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-action-cancel {
        background: #f3f4f6;
        color: #4b5563;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="modal-overlay" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="modal-content">
        <div class="modal-header bg-gray-50 border-b border-gray-200 px-5 py-4 flex justify-between items-center">
            <h3 class="font-bold text-gray-800 text-lg">Edit Tugas</h3>
            <button onclick="this.closest('#editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>

        <form action="<?= base_url('tugas/update') ?>" method="POST" class="p-5">
            <input type="hidden" name="id" value="<?= $tugas['id'] ?>">

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($tugas['judul']) ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full p-2 border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500"><?= htmlspecialchars($tugas['deskripsi']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                <input type="datetime-local" name="deadline" value="<?= date('Y-m-d\TH:i', strtotime($tugas['deadline'])) ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-1 focus:ring-primary-500" required>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="this.closest('#editModal').classList.add('hidden')" class="btn-action-cancel">Batal</button>
                <button type="submit" class="btn-action-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>