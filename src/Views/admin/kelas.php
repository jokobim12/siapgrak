<?php

/**
 * Admin Kelas View
 */
$title = 'Kelola Kelas';
$pageTitle = 'Kelola Kelas';
ob_start();
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Daftar Kelas</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Kelas
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Semester</th>
                        <th>Mahasiswa</th>
                        <th>Mata Kuliah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kelasList as $k): ?>
                        <tr>
                            <td class="font-medium"><?= $k['nama_kelas'] ?></td>
                            <td class="text-sm text-gray-500"><?= $k['semester_nama'] ?></td>
                            <td><span class="badge-primary"><?= $k['total_mahasiswa'] ?></span></td>
                            <td><span class="badge bg-gray-100 text-gray-800"><?= $k['total_matkul'] ?></span></td>
                            <td>
                                <form method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                    <button class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50">
    <div class="modal-overlay" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="modal p-6">
        <h3 class="text-lg font-semibold mb-4">Tambah Kelas</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="label">Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="input" placeholder="TI-2A" required>
                </div>
                <div>
                    <label class="label">Semester</label>
                    <select name="semester_id" class="input" required>
                        <?php foreach ($semesters as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Deskripsi</label>
                    <textarea name="deskripsi" class="input" rows="2"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="this.closest('.modal').parentElement.classList.add('hidden')" class="btn-outline flex-1">Batal</button>
                <button type="submit" class="btn-primary flex-1">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layout.php';
?>