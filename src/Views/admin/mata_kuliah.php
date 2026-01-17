<?php

/**
 * Admin Mata Kuliah View
 */
$title = 'Kelola Mata Kuliah';
$pageTitle = 'Kelola Mata Kuliah';
ob_start();
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Daftar Mata Kuliah</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Mata Kuliah
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Dosen</th>
                        <th>Pertemuan</th>
                        <th>Materi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mataKuliahList as $mk): ?>
                        <tr>
                            <td class="font-mono text-sm"><?= $mk['kode_mk'] ?></td>
                            <td class="font-medium"><?= $mk['nama_mk'] ?></td>
                            <td class="text-sm text-gray-500"><?= $mk['nama_kelas'] ?></td>
                            <td class="text-sm"><?= $mk['dosen'] ?: '-' ?></td>
                            <td><span class="badge-primary"><?= $mk['total_pertemuan'] ?></span></td>
                            <td><span class="badge bg-gray-100 text-gray-800"><?= $mk['total_materi'] ?></span></td>
                            <td>
                                <form method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $mk['id'] ?>">
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
        <h3 class="text-lg font-semibold mb-4">Tambah Mata Kuliah</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Kode MK</label>
                        <input type="text" name="kode_mk" class="input" placeholder="TI101" required>
                    </div>
                    <div>
                        <label class="label">SKS</label>
                        <input type="number" name="sks" class="input" value="3" min="1" max="6">
                    </div>
                </div>
                <div>
                    <label class="label">Nama Mata Kuliah</label>
                    <input type="text" name="nama_mk" class="input" placeholder="Algoritma Pemrograman" required>
                </div>
                <div>
                    <label class="label">Kelas</label>
                    <select name="kelas_id" class="input" required>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?> - <?= $k['semester_nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Dosen Pengampu</label>
                    <input type="text" name="dosen" class="input" placeholder="Nama Dosen">
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