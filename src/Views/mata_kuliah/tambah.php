<?php

/**
 * Tambah Mata Kuliah
 */
$title = 'Tambah Mata Kuliah';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center gap-4">
        <a href="<?= base_url('mata-kuliah') ?>" class="p-2 -ml-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-800">Tambah Mata Kuliah</h1>
    </div>

    <div class="card">
        <form method="POST" class="p-4 space-y-4">
            <div>
                <label class="label">Nama Mata Kuliah</label>
                <input type="text" name="nama" class="input" placeholder="Contoh: Algoritma dan Pemrograman" required autofocus>
            </div>

            <div>
                <label class="label">Semester</label>
                <select name="semester_id" class="input" required>
                    <option value="">Pilih Semester</option>
                    <?php foreach ($semesters as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= $s['nama'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                <i class="fas fa-info-circle mr-2"></i>
                18 pertemuan (P1-P18) akan otomatis dibuat
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>