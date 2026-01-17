<?php

/**
 * Daftar Tugas - Clean Design
 */
$title = 'Tugas';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Tugas</h1>
            <!-- Filter Dropdown -->
            <select onchange="window.location.href=this.value"
                class="text-sm border-gray-200 rounded-lg py-1.5 pl-3 pr-8 bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                <option value="<?= base_url('tugas') ?>" <?= $filter === 'all' ? 'selected' : '' ?>>Semua</option>
                <option value="<?= base_url('tugas?filter=pending') ?>" <?= $filter === 'pending' ? 'selected' : '' ?>>Belum Selesai</option>
                <option value="<?= base_url('tugas?filter=submitted') ?>" <?= $filter === 'submitted' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <button onclick="document.getElementById('createTaskModal').classList.remove('hidden')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Baru</span>
        </button>
    </div>

    <!-- Todo List -->
    <?php if (!empty($tugasList)): ?>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="divide-y divide-gray-100">
                <?php foreach ($tugasList as $t):
                    $isDone = !empty($t['submission_id']);
                    $isLate = (!$isDone && $t['sisa_hari'] < 0);
                    $hasDeadline = !empty($t['deadline']);
                    $deadlineDate = $hasDeadline ? date('d M H:i', strtotime($t['deadline'])) : '-';
                ?>
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors <?= $isLate && !$isDone ? 'border-l-4 border-red-500' : '' ?>">
                        <!-- Checkbox -->
                        <form action="<?= base_url($isDone ? 'tugas/uncheck' : 'tugas/submit') ?>" method="POST" class="flex-shrink-0">
                            <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                            <button type="submit"
                                class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all
                                           <?= $isDone
                                                ? 'bg-green-500 border-green-500 text-white'
                                                : ($isLate ? 'border-red-400 hover:border-red-500' : 'border-gray-300 hover:border-blue-500') ?>">
                                <?php if ($isDone): ?>
                                    <i class="fas fa-check text-xs"></i>
                                <?php endif; ?>
                            </button>
                        </form>

                        <!-- Content -->
                        <a href="<?= base_url('tugas/detail?id=' . $t['id']) ?>" class="flex-1 min-w-0 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 text-sm truncate <?= $isDone ? 'line-through text-gray-400' : '' ?>">
                                    <?= $t['judul'] ?>
                                </h3>
                                <p class="text-xs text-gray-500 truncate"><?= $t['nama_mk'] ?></p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <?php if ($isDone): ?>
                                    <span class="text-xs text-green-600 font-medium"><i class="fas fa-check mr-1"></i>Selesai</span>
                                <?php elseif ($isLate): ?>
                                    <span class="text-xs text-red-600 font-medium">Telat <?= abs($t['sisa_hari']) ?> hari</span>
                                <?php elseif (!$hasDeadline): ?>
                                    <span class="text-xs text-gray-400">Tanpa deadline</span>
                                <?php elseif ($t['sisa_hari'] == 0): ?>
                                    <span class="text-xs text-amber-600 font-medium">Hari ini</span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-600"><?= $deadlineDate ?></span>
                                    <p class="text-[10px] text-gray-400"><?= $t['sisa_hari'] ?> hari lagi</p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <p class="text-xs text-gray-400 text-center">
            Menampilkan <?= count($tugasList) ?> tugas
        </p>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Tidak ada tugas</h3>
            <p class="text-sm text-gray-500 mb-4">Mulai tambahkan tugas baru Anda</p>
            <button onclick="document.getElementById('createTaskModal').classList.add('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus"></i>Tambah Tugas
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Create Task -->
<div id="createTaskModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Tambah Tugas</h3>
            <button onclick="this.closest('#createTaskModal').classList.add('hidden')" class="p-1 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="<?= base_url('tugas/create') ?>" method="POST" class="p-5 space-y-4">
            <input type="hidden" name="redirect_url" value="tugas">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Semester</label>
                <select id="todo_semester_filter" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">Semua Semester</option>
                    <?php foreach ($semesters as $sem): ?>
                        <option value="<?= $sem['id'] ?>"><?= $sem['nama'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah *</label>
                <select name="mata_kuliah_id" id="todo_mk_select" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Pilih...</option>
                    <?php foreach ($allMatkul as $mk): ?>
                        <option value="<?= $mk['id'] ?>" data-semester="<?= $mk['semester_id'] ?>"><?= $mk['nama_mk'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul *</label>
                <input type="text" name="judul" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Kuis 1" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="2" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Catatan..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deadline *</label>
                <input type="datetime-local" name="deadline" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="this.closest('#createTaskModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const semesterFilter = document.getElementById('todo_semester_filter');
        const mkSelect = document.getElementById('todo_mk_select');

        if (semesterFilter && mkSelect) {
            const allOptions = Array.from(mkSelect.options);

            semesterFilter.addEventListener('change', function() {
                const selectedSem = this.value;
                mkSelect.innerHTML = '<option value="">Pilih...</option>';

                allOptions.forEach(opt => {
                    if (opt.value === "") return;
                    const semId = opt.getAttribute('data-semester');
                    if (selectedSem === 'all' || semId === selectedSem) {
                        mkSelect.appendChild(opt.cloneNode(true));
                    }
                });
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>