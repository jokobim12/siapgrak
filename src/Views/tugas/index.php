<?php

/**
 * Daftar Tugas
 */
$title = 'Tugas';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Tugas & Todo</h1>
        <button onclick="document.getElementById('createTaskModal').classList.remove('hidden')"
            class="btn-primary text-sm px-4 py-2">
            <i class="fas fa-plus mr-2"></i>Baru
        </button>
    </div>

    <!-- Filter Stats -->
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
        <a href="<?= base_url('tugas') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'all' ? 'bg-primary-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
            Semua
        </a>
        <a href="<?= base_url('tugas?filter=pending') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'pending' ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
            Belum Selesai
        </a>
        <a href="<?= base_url('tugas?filter=submitted') ?>"
            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors <?= $filter === 'submitted' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
            Selesai
        </a>
    </div>

    <!-- Todo List -->
    <?php if (!empty($tugasList)): ?>
        <div class="space-y-4 mt-6">
            <?php foreach ($tugasList as $t):
                $isDone = !empty($t['submission_id']);
                $isLate = (!$isDone && $t['sisa_hari'] < 0);
            ?>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-start gap-4 transition-all hover:shadow-md relative overflow-hidden group">
                    <?php if ($isLate && !$isDone): ?>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                    <?php endif; ?>

                    <!-- Checkbox Action -->
                    <form action="<?= base_url($isDone ? 'tugas/uncheck' : 'tugas/submit') ?>" method="POST" class="flex-shrink-0 pt-1 z-10 relative">
                        <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                        <button type="submit"
                            title="<?= $isDone ? 'Tandai Belum Selesai' : 'Tandai Selesai' ?>"
                            style="width: 26px; height: 26px; border-radius: 50%; border: 2px solid <?= $isDone ? '#22c55e' : ($isLate ? '#f87171' : '#d1d5db') ?>; background-color: <?= $isDone ? '#22c55e' : 'white' ?>; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                            class="shadow-sm transition-transform hover:scale-110 active:scale-95">
                            <?php if ($isDone): ?>
                                <i class="fas fa-check" style="font-size: 12px;"></i>
                            <?php endif; ?>
                        </button>
                    </form>

                    <a href="<?= base_url('tugas/detail?id=' . $t['id']) ?>" class="flex-1 block">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-gray-800 text-lg leading-tight <?= $isDone ? 'line-through text-gray-400' : '' ?>">
                                    <?= $t['judul'] ?>
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 font-medium bg-gray-100 inline-block px-2 py-0.5 rounded">
                                    <?= $t['nama_mk'] ?>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-1.5 
                                <?= $isDone ? 'text-green-600' : ($isLate ? 'text-red-600 font-bold' : ($t['sisa_hari'] <= 3 ? 'text-amber-600' : 'text-gray-500')) ?>">
                                <i class="far fa-clock"></i>
                                <span>
                                    <?php
                                    $deadlineDate = date('d M, H:i', strtotime($t['deadline']));
                                    if ($isDone) echo "Selesai";
                                    elseif ($isLate) echo "Telat " . abs($t['sisa_hari']) . " hari ($deadlineDate)";
                                    elseif ($t['sisa_hari'] == 0) echo "Hari ini, $deadlineDate";
                                    else echo "$deadlineDate (" . $t['sisa_hari'] . " hari lagi)";
                                    ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-clipboard-list text-2xl"></i>
            </div>
            <p class="font-medium text-gray-600">Tidak ada tugas</p>
            <p class="text-sm">Mulai tambahkan tugas baru Anda!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Create Task -->
<!-- Custom Style for Modal -->
<style>
    /* Overlay Background - Solid Dark Transparent */
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 40;
    }

    /* Modal Container styling */
    .modal-content {
        max-width: 480px;
        /* Diperkecil dari 600px */
        width: 90%;
        margin: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 50;
        /* Pastikan di atas overlay */
        overflow: hidden;
    }

    .modal-header {
        padding: 16px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 0.825rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        /* Lebih kotak sedikit biar rapi */
        padding: 8px 12px;
        font-size: 0.875rem;
        transition: all 0.2s;
        background-color: #fff;
        color: #1f2937;
    }

    .form-control:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    }

    .btn-submit {
        background-color: #2563eb;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-cancel {
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
    }
</style>

<div id="createTaskModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Overlay Click Close -->
    <div class="modal-overlay" onclick="this.parentElement.classList.add('hidden')"></div>

    <!-- Modal Content -->
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Tambah Tugas</h3>
            </div>
            <button onclick="this.closest('#createTaskModal').classList.add('hidden')" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="<?= base_url('tugas/create') ?>" method="POST" class="modal-body">
            <input type="hidden" name="redirect_url" value="tugas">

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-book mr-1 text-primary-600"></i>Mata Kuliah
                </label>
                <select name="mata_kuliah_id" class="form-control" required>
                    <option value="" disabled selected>Pilih...</option>
                    <?php if (!empty($allMatkul)): ?>
                        <?php foreach ($allMatkul as $mk): ?>
                            <option value="<?= $mk['id'] ?>"><?= $mk['nama_mk'] ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Belum ada MK</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-pen mr-1 text-primary-600"></i>Judul
                </label>
                <input type="text" name="judul" class="form-control" placeholder="Contoh: Kuis 1" required>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-align-left mr-1 text-primary-600"></i>Deskripsi
                </label>
                <textarea name="deskripsi" rows="3" class="form-control" placeholder="Catatan..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <i class="far fa-clock mr-1 text-primary-600"></i>Deadline
                </label>
                <input type="datetime-local" name="deadline" class="form-control" required>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="this.closest('#createTaskModal').classList.add('hidden')" class="btn-cancel">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>