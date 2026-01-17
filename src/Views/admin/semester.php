<?php

/**
 * Admin Semester View
 */
$title = 'Kelola Semester';
$pageTitle = 'Kelola Semester';
ob_start();
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Daftar Semester</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Semester
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tahun Ajaran</th>
                        <th>Periode</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($semesters as $s): ?>
                        <tr>
                            <td class="font-medium"><?= $s['nama'] ?></td>
                            <td><?= $s['tahun_ajaran'] ?></td>
                            <td class="capitalize"><?= $s['periode'] ?></td>
                            <td class="text-sm text-gray-500">
                                <?= formatDate($s['tanggal_mulai']) ?> - <?= formatDate($s['tanggal_selesai']) ?>
                            </td>
                            <td>
                                <?php if ($s['is_active']): ?>
                                    <span class="badge-success">Aktif</span>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="set_active">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="text-xs text-primary-600 hover:underline">Set Aktif</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
        <h3 class="text-lg font-semibold mb-4">Tambah Semester</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="label">Nama Semester</label>
                    <input type="text" name="nama" class="input" placeholder="Semester 1 - Ganjil 2024/2025" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" class="input" placeholder="2024/2025" required>
                    </div>
                    <div>
                        <label class="label">Periode</label>
                        <select name="periode" class="input" required>
                            <option value="ganjil">Ganjil</option>
                            <option value="genap">Genap</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label">Nomor Semester</label>
                    <input type="number" name="nomor_semester" class="input" min="1" max="8" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="input" required>
                    </div>
                    <div>
                        <label class="label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="input" required>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active">
                    <label for="is_active" class="text-sm">Set sebagai semester aktif</label>
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