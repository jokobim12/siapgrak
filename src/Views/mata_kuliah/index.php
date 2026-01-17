<?php

/**
 * Mata Kuliah Index View
 */
$title = 'Mata Kuliah';
$pageTitle = 'Mata Kuliah';
ob_start();
?>

<div class="space-y-6">
    <!-- Filter Section -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Semester</label>
                <select name="semester_id" class="input" onchange="this.form.submit()">
                    <option value="">Semua Semester</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?= $semester['id'] ?>" <?= $selectedSemesterId == $semester['id'] ? 'selected' : '' ?>>
                            <?= $semester['nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="label">Kelas</label>
                <select name="kelas_id" class="input" onchange="this.form.submit()">
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($kelasList as $kelas): ?>
                        <option value="<?= $kelas['id'] ?>" <?= $selectedKelasId == $kelas['id'] ? 'selected' : '' ?>>
                            <?= $kelas['nama_kelas'] ?> - <?= $kelas['semester_nama'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Mata Kuliah Grid -->
    <?php if (!empty($mataKuliahList)): ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($mataKuliahList as $mk): ?>
                <a href="<?= base_url('mata-kuliah/detail?id=' . $mk['id']) ?>" class="card-hover group">
                    <!-- Header with gradient -->
                    <div class="h-24 gradient-primary rounded-t-xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs text-white">
                                <?= $mk['kode_mk'] ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                            <?= $mk['nama_mk'] ?>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1"><?= $mk['nama_kelas'] ?></p>

                        <?php if ($mk['dosen']): ?>
                            <div class="flex items-center gap-2 mt-3 text-sm text-gray-600">
                                <i class="fas fa-user-tie"></i>
                                <span><?= $mk['dosen'] ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                <i class="fas fa-chalkboard"></i>
                                <span><?= $mk['total_pertemuan'] ?> Pertemuan</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-sm text-gray-500">
                                <i class="fas fa-file-alt"></i>
                                <span><?= $mk['total_materi'] ?> Materi</span>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span><?= round(($mk['total_materi'] / 18) * 100) ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill" style="width: <?= min(100, round(($mk['total_materi'] / 18) * 100)) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php elseif ($selectedKelasId): ?>
        <div class="card p-8 text-center">
            <i class="fas fa-book-open text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Tidak ada mata kuliah di kelas ini</p>
        </div>
    <?php else: ?>
        <div class="card p-8 text-center">
            <i class="fas fa-hand-pointer text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Pilih kelas untuk melihat mata kuliah</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>