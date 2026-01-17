<?php

/**
 * Jadwal View
 */
$title = 'Jadwal Kuliah';
$pageTitle = 'Jadwal Kuliah';
ob_start();
?>

<div class="space-y-6">
    <!-- Day Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        <?php
        $hariLabels = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            'sabtu' => 'Sabtu'
        ];
        ?>
        <?php foreach ($hariList as $hari): ?>
            <button onclick="showDay('<?= $hari ?>')"
                id="tab-<?= $hari ?>"
                class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition-all
                       <?= $hari === $hariIni ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100' ?>
                       day-tab">
                <?= $hariLabels[$hari] ?>
                <?php if ($hari === $hariIni): ?>
                    <span class="ml-1 text-xs opacity-75">(Hari Ini)</span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Schedule Cards -->
    <?php foreach ($hariList as $hari): ?>
        <div id="schedule-<?= $hari ?>"
            class="day-schedule <?= $hari !== $hariIni ? 'hidden' : '' ?>">
            <div class="card">
                <div class="p-4 border-b border-gray-200 gradient-primary rounded-t-xl">
                    <h2 class="text-lg font-semibold text-white">
                        <i class="fas fa-calendar-day mr-2"></i>
                        <?= $hariLabels[$hari] ?>
                    </h2>
                </div>

                <?php if (!empty($jadwalByHari[$hari])): ?>
                    <div class="divide-y divide-gray-100">
                        <?php foreach ($jadwalByHari[$hari] as $jadwal): ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <!-- Time -->
                                    <div class="text-center min-w-[80px] p-3 bg-primary-50 rounded-lg">
                                        <p class="text-lg font-bold text-primary-600"><?= substr($jadwal['jam_mulai'], 0, 5) ?></p>
                                        <p class="text-xs text-primary-500"><?= substr($jadwal['jam_selesai'], 0, 5) ?></p>
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900"><?= $jadwal['nama_mk'] ?></h3>
                                        <p class="text-sm text-gray-500"><?= $jadwal['kode_mk'] ?></p>
                                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-500">
                                            <?php if ($jadwal['dosen']): ?>
                                                <span class="flex items-center gap-1">
                                                    <i class="fas fa-user-tie"></i>
                                                    <?= $jadwal['dosen'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($jadwal['ruangan']): ?>
                                                <span class="flex items-center gap-1">
                                                    <i class="fas fa-door-open"></i>
                                                    <?= $jadwal['ruangan'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="flex items-center gap-1">
                                                <i class="fas fa-users"></i>
                                                <?= $jadwal['nama_kelas'] ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Duration Badge -->
                                    <?php
                                    $start = strtotime($jadwal['jam_mulai']);
                                    $end = strtotime($jadwal['jam_selesai']);
                                    $duration = ($end - $start) / 60; // in minutes
                                    ?>
                                    <div class="flex-shrink-0">
                                        <span class="badge-primary"><?= $duration ?> menit</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-coffee text-5xl mb-4 text-gray-300"></i>
                        <p>Tidak ada jadwal kuliah</p>
                        <p class="text-sm mt-1">Hari libur atau tidak ada kelas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function showDay(day) {
        // Hide all schedules
        document.querySelectorAll('.day-schedule').forEach(el => el.classList.add('hidden'));
        // Show selected schedule
        document.getElementById('schedule-' + day).classList.remove('hidden');

        // Update tabs
        document.querySelectorAll('.day-tab').forEach(el => {
            el.classList.remove('bg-primary-600', 'text-white');
            el.classList.add('bg-white', 'text-gray-600');
        });
        const activeTab = document.getElementById('tab-' + day);
        activeTab.classList.remove('bg-white', 'text-gray-600');
        activeTab.classList.add('bg-primary-600', 'text-white');
    }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>