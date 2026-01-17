<?php

/**
 * Detail Tugas - Clean Design
 */
$title = $tugas['judul'];
$isDone = !empty($submission);
$isLate = !$isDone && $tugas['sisa_hari'] < 0;
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= base_url('tugas') ?>" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg lg:text-xl font-bold text-gray-900 truncate"><?= $tugas['judul'] ?></h1>
                <p class="text-xs text-gray-500"><?= $tugas['nama_mk'] ?> · P<?= $tugas['nomor_pertemuan'] ?></p>
            </div>
        </div>
    </div>

    <!-- Content Card -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <!-- Status Bar -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between <?= $isDone ? 'bg-green-50' : ($isLate ? 'bg-red-50' : 'bg-gray-50') ?>">
            <div class="flex items-center gap-2">
                <?php if ($isDone): ?>
                    <span class="flex items-center gap-2 text-sm font-medium text-green-700">
                        <i class="fas fa-check-circle"></i> Selesai
                    </span>
                    <span class="text-xs text-gray-500">pada <?= date('d M Y H:i', strtotime($submission['submitted_at'])) ?></span>
                <?php elseif ($isLate): ?>
                    <span class="flex items-center gap-2 text-sm font-medium text-red-700">
                        <i class="fas fa-exclamation-circle"></i> Terlambat <?= abs($tugas['sisa_hari']) ?> hari
                    </span>
                <?php else: ?>
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-600">
                        <i class="far fa-clock"></i> Belum Selesai
                    </span>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('editModal').classList.remove('hidden')"
                    class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <form id="deleteTugas<?= $tugas['id'] ?>" action="<?= base_url('tugas/delete') ?>" method="POST">
                    <input type="hidden" name="id" value="<?= $tugas['id'] ?>">
                    <button type="button" onclick="showConfirm('Hapus Tugas', 'Apakah Anda yakin ingin menghapus tugas ini secara permanen?', '', true, 'deleteTugas<?= $tugas['id'] ?>')" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="p-4 space-y-4">
            <!-- Deadline -->
            <div class="flex items-center gap-4 p-4 bg-blue-50 rounded-lg">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="far fa-calendar-alt text-white"></i>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium uppercase">Deadline</p>
                    <p class="text-lg font-bold text-gray-900"><?= formatDateTime($tugas['deadline']) ?></p>
                    <?php if (!$isDone && $tugas['sisa_hari'] >= 0): ?>
                        <p class="text-xs text-gray-500">
                            <?= $tugas['sisa_hari'] == 0 ? 'Hari ini' : $tugas['sisa_hari'] . ' hari lagi' ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <?php if ($tugas['deskripsi']): ?>
                <div>
                    <h3 class="text-xs text-gray-500 font-medium uppercase mb-2">Deskripsi</h3>
                    <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4 leading-relaxed">
                        <?= nl2br(htmlspecialchars($tugas['deskripsi'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Button -->
            <form action="<?= base_url($isDone ? 'tugas/uncheck' : 'tugas/submit') ?>" method="POST">
                <input type="hidden" name="tugas_id" value="<?= $tugas['id'] ?>">
                <button type="submit"
                    class="w-full py-3 rounded-lg font-semibold text-white transition-colors flex items-center justify-center gap-2
                               <?= $isDone ? 'bg-gray-500 hover:bg-gray-600' : 'bg-blue-600 hover:bg-blue-700' ?>">
                    <?php if ($isDone): ?>
                        <i class="fas fa-undo"></i> Batalkan Selesai
                    <?php else: ?>
                        <i class="fas fa-check-double"></i> Tandai Selesai
                    <?php endif; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Edit Tugas</h3>
            <button onclick="this.closest('#editModal').classList.add('hidden')" class="p-1 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="<?= base_url('tugas/update') ?>" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="id" value="<?= $tugas['id'] ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($tugas['judul']) ?>"
                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($tugas['deskripsi']) ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label>
                <input type="datetime-local" name="deadline" value="<?= date('Y-m-d\TH:i', strtotime($tugas['deadline'])) ?>"
                    class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="this.closest('#editModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>