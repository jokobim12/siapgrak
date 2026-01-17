<?php

/**
 * Admin Jadwal View
 */
$title = 'Kelola Jadwal';
$pageTitle = 'Kelola Jadwal';
ob_start();
$hariOptions = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">Daftar Jadwal</h2>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Jadwal
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Mata Kuliah</th>
                        <th>Kelas</th>
                        <th>Ruangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jadwalList as $j): ?>
                        <tr>
                            <td class="font-medium capitalize"><?= $j['hari'] ?></td>
                            <td class="text-sm"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></td>
                            <td><?= $j['nama_mk'] ?> (<?= $j['kode_mk'] ?>)</td>
                            <td class="text-sm text-gray-500"><?= $j['nama_kelas'] ?></td>
                            <td><?= $j['ruangan'] ?: '-' ?></td>
                            <td>
                                <form method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
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
        <h3 class="text-lg font-semibold mb-4">Tambah Jadwal</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="label">Kelas</label>
                    <select name="kelas_id" class="input" required>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?> - <?= $k['semester_nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Mata Kuliah</label>
                    <select name="mata_kuliah_id" class="input" required>
                        <?php foreach ($mataKuliahList as $mk): ?>
                            <option value="<?= $mk['id'] ?>"><?= $mk['nama_mk'] ?> - <?= $mk['nama_kelas'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label">Hari</label>
                    <select name="hari" class="input" required>
                        <?php foreach ($hariOptions as $val => $label): ?>
                            <option value="<?= $val ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="input" required>
                    </div>
                    <div>
                        <label class="label">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="input" required>
                    </div>
                </div>
                <div>
                    <label class="label">Ruangan</label>
                    <input type="text" name="ruangan" class="input" placeholder="Lab Komputer 1">
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