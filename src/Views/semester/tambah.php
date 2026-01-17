<?php

/**
 * Tambah Semester
 */
$title = 'Tambah Semester';
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <div class="flex items-center gap-4">
        <a href="<?= base_url('semester') ?>" class="p-2 -ml-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-800">Tambah Semester</h1>
    </div>

    <div class="card">
        <form method="POST" class="p-4 space-y-4">
            <div>
                <label class="label">Nama Semester</label>
                <input type="text" name="nama" class="input" placeholder="Contoh: Semester 3 - Ganjil 2024/2025" required autofocus>
                <p class="text-xs text-gray-500 mt-1">Masukkan nama semester sesuai dengan yang ada di Edlink</p>
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