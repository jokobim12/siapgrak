<?php

/**
 * Jadwal Mingguan
 */
$title = 'Jadwal';
$days = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu'];
$today = strtolower(date('l'));
$dayMap = ['monday' => 'senin', 'tuesday' => 'selasa', 'wednesday' => 'rabu', 'thursday' => 'kamis', 'friday' => 'jumat', 'saturday' => 'sabtu'];
$todayId = $dayMap[$today] ?? '';

ob_start();
?>

<div class="space-y-6 pb-20 lg:pb-0">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Jadwal Mingguan</h1>
        <a href="<?= base_url('jadwal/tambah') ?>" class="btn-primary px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($days as $dayKey => $dayName): ?>
            <?php
            $isToday = $dayKey === $todayId;
            $schedules = $jadwalByDay[$dayKey] ?? [];
            ?>
            <div class="bg-white rounded-xl shadow-sm border <?= $isToday ? 'border-primary-300 ring-1 ring-primary-100' : 'border-gray-200' ?> overflow-hidden flex flex-col h-full">
                <!-- Header Hari -->
                <div class="p-3 border-b flex items-center justify-between <?= $isToday ? 'bg-primary-50 border-primary-100' : 'bg-gray-50 border-gray-100' ?>">
                    <h3 class="font-semibold <?= $isToday ? 'text-primary-700' : 'text-gray-700' ?>">
                        <?= $dayName ?>
                    </h3>
                    <?php if ($isToday): ?>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 font-medium">Hari ini</span>
                    <?php endif; ?>
                </div>

                <!-- List Jadwal -->
                <div class="flex-1 p-3 space-y-3">
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $j): ?>
                            <div class="group relative bg-white border border-gray-100 rounded-lg p-3 hover:shadow-md transition-shadow">
                                <!-- Top: Time & Delete -->
                                <div class="flex justify-between items-start mb-1">
                                    <div class="text-xs font-semibold text-primary-600 bg-primary-50 px-2 py-1 rounded">
                                        <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                                    </div>
                                    <form action="<?= base_url('jadwal/hapus') ?>" method="POST" onsubmit="return confirm('Hapus jadwal ini?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>

                                <h4 class="font-bold text-gray-800 text-sm mb-1 leading-tight"><?= $j['nama_mk'] ?></h4>

                                <div class="space-y-1 text-xs text-gray-500">
                                    <?php if ($j['ruangan']): ?>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-map-marker-alt w-4 text-center text-gray-400"></i>
                                            <span><?= $j['ruangan'] ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($j['dosen'] && $j['dosen'] != '-'): ?>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-user-tie w-4 text-center text-gray-400"></i>
                                            <span><?= $j['dosen'] ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($j['korti'] && $j['korti'] != '-'): ?>
                                        <div class="flex items-center gap-2" title="Korti / PJ">
                                            <i class="fas fa-user-graduate w-4 text-center text-gray-400"></i>
                                            <span class="text-gray-600">PJ: <?= $j['korti'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 py-6">
                            <i class="far fa-calendar-times text-2xl mb-2 opacity-50"></i>
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