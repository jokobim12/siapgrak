<?php

/**
 * Jadwal Mingguan - Card per Hari
 */
$title = 'Jadwal';
$days = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
$today = strtolower(date('l'));
$dayMap = ['monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu'];
$todayId = $dayMap[$today] ?? '';

ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">Jadwal</h1>
            <?php if (isset($semesterAktif) && $semesterAktif): ?>
                <span class="text-xs px-2 py-1 rounded-lg bg-blue-100 text-blue-700 font-medium">
                    <?= htmlspecialchars($semesterAktif['nama'] ?? 'Semester ' . $semesterAktif['nomor']) ?>
                </span>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('jadwal/tambah') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Tambah</span>
        </a>
    </div>

    <!-- Day Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($days as $dayKey => $dayName): ?>
            <?php
            $isToday = $dayKey === $todayId;
            $schedules = $jadwalByDay[$dayKey] ?? [];
            ?>
            <div class="bg-white rounded-xl border <?= $isToday ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-200' ?> overflow-hidden">
                <!-- Day Header -->
                <div class="px-4 py-2.5 border-b <?= $isToday ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-100' ?> flex items-center justify-between">
                    <h3 class="font-semibold <?= $isToday ? 'text-blue-700' : 'text-gray-700' ?>"><?= $dayName ?></h3>
                    <?php if ($isToday): ?>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-600 text-white font-medium">Hari ini</span>
                    <?php endif; ?>
                </div>

                <!-- Schedules -->
                <div class="p-3 space-y-2 min-h-[120px]">
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $j): ?>
                            <div class="group relative bg-gray-50 hover:bg-white border border-gray-100 hover:border-blue-200 rounded-lg p-3 transition-all">
                                <!-- Time Badge -->
                                <div class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded mb-2">
                                    <i class="far fa-clock text-[10px]"></i>
                                    <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                                </div>

                                <!-- Title -->
                                <h4 class="font-semibold text-gray-900 text-sm leading-tight mb-1.5"><?= $j['nama_mk'] ?></h4>

                                <!-- Details -->
                                <div class="space-y-0.5 text-xs text-gray-500">
                                    <?php if ($j['ruangan']): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fas fa-map-marker-alt w-3 text-center text-gray-400"></i>
                                            <span><?= $j['ruangan'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($j['dosen'] && $j['dosen'] != '-'): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fas fa-user-tie w-3 text-center text-gray-400"></i>
                                            <span><?= $j['dosen'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($j['korti'] && $j['korti'] != '-'): ?>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fas fa-user-graduate w-3 text-center text-gray-400"></i>
                                            <span>PJ: <?= $j['korti'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Delete Button -->
                                <form id="deleteJadwal<?= $j['id'] ?>" action="<?= base_url('jadwal/hapus') ?>" method="POST"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                    <button type="button" onclick="showConfirm('Hapus Jadwal', 'Apakah Anda yakin ingin menghapus jadwal ini dari hari <?= ucfirst($dayKey) ?>?', '', true, 'deleteJadwal<?= $j['id'] ?>')" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="h-full flex flex-col items-center justify-center text-gray-300 py-6">
                            <i class="far fa-calendar text-2xl mb-1"></i>
                            <span class="text-xs">Kosong</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>