<?php

/**
 * Admin Kelas Mahasiswa View
 */
$title = 'Assign Mahasiswa';
$pageTitle = 'Assign Mahasiswa ke Kelas';
ob_start();
?>

<div class="space-y-6">
    <!-- Select Kelas -->
    <div class="card p-4">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="label">Pilih Kelas</label>
                <select name="kelas_id" class="input" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>>
                            <?= $k['nama_kelas'] ?> - <?= $k['semester_nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($selectedKelasId): ?>
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Mahasiswa di Kelas -->
            <div class="card">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold">Mahasiswa di Kelas (<?= count($kelasMahasiswa) ?>)</h3>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    <?php if (!empty($kelasMahasiswa)): ?>
                        <?php foreach ($kelasMahasiswa as $km): ?>
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <p class="font-medium"><?= $km['nama'] ?></p>
                                    <p class="text-sm text-gray-500"><?= $km['nim'] ?> - <?= $km['email'] ?></p>
                                </div>
                                <form method="POST" onsubmit="return confirm('Hapus dari kelas?')">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id" value="<?= $km['id'] ?>">
                                    <input type="hidden" name="kelas_id" value="<?= $selectedKelasId ?>">
                                    <button class="text-red-600 hover:text-red-800"><i class="fas fa-times"></i></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="p-4 text-gray-500 text-center">Belum ada mahasiswa di kelas ini</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tambah Mahasiswa -->
            <div class="card">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold">Tambah Mahasiswa</h3>
                </div>
                <?php if (!empty($availableMahasiswa)): ?>
                    <form method="POST" class="p-4">
                        <input type="hidden" name="action" value="assign">
                        <input type="hidden" name="kelas_id" value="<?= $selectedKelasId ?>">
                        <div class="space-y-4">
                            <div>
                                <label class="label">Pilih Mahasiswa</label>
                                <select name="mahasiswa_id" class="input" required>
                                    <?php foreach ($availableMahasiswa as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= $m['nim'] ?> - <?= $m['nama'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary w-full">
                                <i class="fas fa-plus mr-2"></i>Tambahkan ke Kelas
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="p-4 text-gray-500 text-center">Semua mahasiswa sudah terdaftar di kelas ini</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layout.php';
?>