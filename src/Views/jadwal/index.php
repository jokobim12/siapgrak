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

<div class="space-y-4 pb-20 lg:pb-0">
    <h1 class="text-xl font-bold text-gray-800">Jadwal Mingguan</h1>

    <div class="space-y-4">
        <?php foreach ($days as $dayKey => $dayName): ?>
            <div class="card <?= $dayKey === $todayId ? 'border-primary-300 bg-primary-50' : '' ?>">
                <div class="p-3 border-b border-gray-200 flex items-center justify-between <?= $dayKey === $todayId ? 'bg-primary-100' : 'bg-gray-50' ?>">
                    <h3 class="font-semibold <?= $dayKey === $todayId ? 'text-primary-700' : 'text-gray-700' ?>">
                        <?= $dayName ?>
                    </h3>
                    <?php if ($dayKey === $todayId): ?>
                        <span class="badge-primary">Hari ini</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($jadwalByDay[$dayKey])): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($jadwalByDay[$dayKey] as $j): ?>
                            <div class="p-3 flex items-center gap-4">
                                <div class="text-center w-20 flex-shrink-0">
                                    <p class="text-sm font-medium text-gray-800"><?= substr($j['jam_mulai'], 0, 5) ?></p>
                                    <p class="text-xs text-gray-400">-</p>
                                    <p class="text-sm font-medium text-gray-800"><?= substr($j['jam_selesai'], 0, 5) ?></p>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-800 truncate"><?= $j['nama_mk'] ?></p>
                                    <?php if ($j['ruangan']): ?>
                                        <p class="text-sm text-gray-500">
                                            <i class="fas fa-map-marker-alt mr-1"></i><?= $j['ruangan'] ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 text-center text-gray-400 text-sm">
                        Tidak ada jadwal
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>