<?php

/**
 * Mata Kuliah Detail View
 * Grid Pertemuan P1-P18
 */
$title = $mataKuliah['nama_mk'];
$pageTitle = $mataKuliah['nama_mk'];
ob_start();
?>

<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500">
        <a href="<?= base_url('mata-kuliah') ?>" class="hover:text-primary-600">Mata Kuliah</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900"><?= $mataKuliah['nama_mk'] ?></span>
    </nav>

    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="h-32 gradient-primary relative">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>
        <div class="p-6 -mt-12 relative">
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="w-20 h-20 rounded-2xl bg-white shadow-lg flex items-center justify-center">
                    <span class="text-2xl font-bold gradient-text"><?= strtoupper(substr($mataKuliah['nama_mk'], 0, 2)) ?></span>
                </div>
                <div class="flex-1">
                    <span class="badge-primary mb-2"><?= $mataKuliah['kode_mk'] ?></span>
                    <h1 class="text-2xl font-bold text-gray-900"><?= $mataKuliah['nama_mk'] ?></h1>
                    <p class="text-gray-500"><?= $mataKuliah['nama_kelas'] ?> - <?= $mataKuliah['semester_nama'] ?></p>
                </div>
                <?php if ($mataKuliah['dosen']): ?>
                    <div class="flex items-center gap-2 text-gray-600">
                        <i class="fas fa-user-tie"></i>
                        <span><?= $mataKuliah['dosen'] ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Pertemuan Grid P1-P18 -->
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-calendar-alt text-primary-500 mr-2"></i>
            Pilih Pertemuan
        </h2>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9 gap-3">
            <?php foreach ($pertemuanList as $pertemuan): ?>
                <?php
                $hasContent = $pertemuan['total_materi'] > 0 || $pertemuan['total_tugas'] > 0;
                $cardClass = $hasContent ? 'pertemuan-card-complete' : 'pertemuan-card';
                ?>
                <a href="<?= base_url('pertemuan?id=' . $pertemuan['id']) ?>"
                    class="<?= $cardClass ?> group">
                    <span class="text-2xl font-bold <?= $hasContent ? 'text-emerald-600' : 'text-gray-400 group-hover:text-primary-600' ?>">
                        P<?= $pertemuan['nomor_pertemuan'] ?>
                    </span>
                    <?php if ($hasContent): ?>
                        <div class="flex items-center gap-1 mt-1 text-xs text-emerald-600">
                            <?php if ($pertemuan['total_materi'] > 0): ?>
                                <span><i class="fas fa-file"></i> <?= $pertemuan['total_materi'] ?></span>
                            <?php endif; ?>
                            <?php if ($pertemuan['total_tugas'] > 0): ?>
                                <span><i class="fas fa-tasks"></i> <?= $pertemuan['total_tugas'] ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="text-xs text-gray-400 mt-1">Kosong</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500">
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 border-gray-200 bg-white"></div>
            <span>Belum ada materi</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded border-2 border-emerald-500 bg-emerald-50"></div>
            <span>Ada materi/tugas</span>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>