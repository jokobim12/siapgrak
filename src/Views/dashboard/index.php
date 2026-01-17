<?php

/**
 * Dashboard View
 */
$title = 'Dashboard';
$pageTitle = 'Dashboard';
ob_start();
?>

<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="card p-6 gradient-primary text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold">Selamat Datang, <?= explode(' ', $user['nama'])[0] ?>!</h2>
                <p class="mt-1 opacity-90">Semester <?= $user['semester_aktif'] ?> - <?= $semesterAktif['nama'] ?? 'Tidak ada semester aktif' ?></p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-4 py-2 bg-white/20 rounded-full text-sm">
                    <i class="fas fa-id-card mr-2"></i>
                    <?= $user['nim'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-book text-primary-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $progressStats['total_matkul'] ?? 0 ?></p>
                    <p class="text-sm text-gray-500">Mata Kuliah</p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $progressStats['pertemuan_selesai'] ?? 0 ?></p>
                    <p class="text-sm text-gray-500">Pertemuan</p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-tasks text-amber-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $progressStats['tugas_selesai'] ?? 0 ?></p>
                    <p class="text-sm text-gray-500">Tugas Selesai</p>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= count($tugasDeadline) ?></p>
                    <p class="text-sm text-gray-500">Tugas Pending</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Jadwal Hari Ini -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">
                        <i class="fas fa-calendar-day text-primary-500 mr-2"></i>
                        Jadwal Hari Ini
                    </h3>
                </div>
                <div class="p-4 space-y-3">
                    <?php if (!empty($jadwalHariIni)): ?>
                        <?php foreach ($jadwalHariIni as $jadwal): ?>
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="text-center min-w-[60px]">
                                    <p class="text-sm font-semibold text-primary-600"><?= substr($jadwal['jam_mulai'], 0, 5) ?></p>
                                    <p class="text-xs text-gray-400"><?= substr($jadwal['jam_selesai'], 0, 5) ?></p>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 text-sm"><?= $jadwal['nama_mk'] ?></p>
                                    <p class="text-xs text-gray-500"><?= $jadwal['ruangan'] ?? '-' ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-6 text-gray-500">
                            <i class="fas fa-coffee text-3xl mb-2"></i>
                            <p class="text-sm">Tidak ada jadwal hari ini</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-4 border-t border-gray-200">
                    <a href="<?= base_url('jadwal') ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Lihat Semua Jadwal <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tugas Mendekati Deadline -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                        Tugas Mendekati Deadline
                    </h3>
                    <a href="<?= base_url('tugas') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                        Lihat Semua
                    </a>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (!empty($tugasDeadline)): ?>
                        <?php foreach ($tugasDeadline as $tugas): ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-medium text-gray-900"><?= $tugas['judul'] ?></h4>
                                            <?php if ($tugas['sudah_submit']): ?>
                                                <span class="badge-success">Sudah Dikumpulkan</span>
                                            <?php elseif ($tugas['sisa_hari'] <= 1): ?>
                                                <span class="badge-danger">Segera!</span>
                                            <?php elseif ($tugas['sisa_hari'] <= 3): ?>
                                                <span class="badge-warning"><?= $tugas['sisa_hari'] ?> hari lagi</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?= $tugas['nama_mk'] ?> - Pertemuan <?= $tugas['nomor_pertemuan'] ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900"><?= formatDate($tugas['deadline']) ?></p>
                                        <p class="text-xs text-gray-500"><?= date('H:i', strtotime($tugas['deadline'])) ?> WITA</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-clipboard-check text-4xl mb-3 text-gray-300"></i>
                            <p>Tidak ada tugas mendekati deadline</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mata Kuliah -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Mata Kuliah Aktif</h3>
            <a href="<?= base_url('mata-kuliah') ?>" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>

        <?php if (!empty($mataKuliahList)): ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach (array_slice($mataKuliahList, 0, 6) as $mk): ?>
                    <a href="<?= base_url('mata-kuliah/detail?id=' . $mk['id']) ?>" class="card-hover p-5">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($mk['nama_mk'], 0, 2)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 truncate"><?= $mk['nama_mk'] ?></h4>
                                <p class="text-sm text-gray-500"><?= $mk['kode_mk'] ?></p>
                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                    <span><i class="fas fa-chalkboard mr-1"></i> <?= $mk['total_pertemuan'] ?> pertemuan</span>
                                    <span><i class="fas fa-file mr-1"></i> <?= $mk['total_materi'] ?> materi</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card p-8 text-center">
                <i class="fas fa-book-open text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Belum ada mata kuliah yang terdaftar</p>
                <p class="text-sm text-gray-400 mt-1">Hubungi admin untuk mendaftarkan Anda ke kelas</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>