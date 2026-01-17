<?php
$title = 'Tambah Jadwal';
$scheduledMatkulIds = $scheduledMatkulIds ?? [];
$activeSemesterId = $activeSemesterId ?? null;
ob_start();
?>

<div class="space-y-4 pb-24 lg:pb-0 max-w-lg mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('jadwal') ?>" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Tambah Jadwal</h1>
        </div>
    </div>

    <?php if (empty($matkul)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
            <i class="fas fa-exclamation-triangle text-amber-500 text-xl mb-2"></i>
            <p class="text-sm text-amber-700 mb-3">Anda belum memiliki Mata Kuliah.</p>
            <a href="<?= base_url('mata-kuliah/tambah') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700">
                <i class="fas fa-plus"></i> Buat Mata Kuliah
            </a>
        </div>
    <?php else: ?>

        <?php if ($activeSemesterId): ?>
            <!-- Info: Semester Locked -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-medium">Semester Terkunci</p>
                    <p class="text-xs mt-1">Anda sudah memiliki jadwal dari semester tertentu. Jadwal baru hanya bisa ditambahkan dari semester yang sama.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <form action="<?= base_url('jadwal/simpan') ?>" method="POST" id="jadwalForm" class="p-4 space-y-4">
                <?= csrf_field() ?>

                <!-- Semester Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                    <select id="semester_filter" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" <?= $activeSemesterId ? 'disabled' : '' ?>>
                        <?php if ($activeSemesterId): ?>
                            <?php foreach ($semesters as $sem): ?>
                                <?php if ($sem['id'] == $activeSemesterId): ?>
                                    <option value="<?= $sem['id'] ?>" selected><?= $sem['nama'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="all">Semua Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>"><?= $sem['nama'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if ($activeSemesterId): ?>
                        <p class="text-xs text-blue-600 mt-1"><i class="fas fa-lock mr-1"></i>Semester terkunci karena sudah ada jadwal</p>
                    <?php endif; ?>
                </div>

                <!-- Mata Kuliah -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah *</label>
                    <select name="mata_kuliah_id" id="mata_kuliah_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Pilih Mata Kuliah</option>
                        <?php foreach ($matkul as $mk):
                            $isScheduled = in_array($mk['id'], $scheduledMatkulIds);
                            $wrongSemester = $activeSemesterId && $mk['semester_id'] != $activeSemesterId;
                            $disabled = $isScheduled || $wrongSemester;
                        ?>
                            <option value="<?= $mk['id'] ?>"
                                data-dosen="<?= $mk['dosen'] ?? '' ?>"
                                data-korti="<?= $mk['korti'] ?? '' ?>"
                                data-semester="<?= $mk['semester_id'] ?>"
                                <?= $disabled ? 'disabled' : '' ?>
                                <?= $disabled ? 'class="text-gray-400"' : '' ?>>
                                <?= $mk['nama_mk'] ?><?= $isScheduled ? ' (sudah dijadwalkan)' : '' ?><?= $wrongSemester ? ' (semester berbeda)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Hari & Waktu -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hari *</label>
                        <select name="hari" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="senin">Senin</option>
                            <option value="selasa">Selasa</option>
                            <option value="rabu">Rabu</option>
                            <option value="kamis">Kamis</option>
                            <option value="jumat">Jumat</option>
                            <option value="sabtu">Sabtu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mulai *</label>
                        <input type="time" name="jam_mulai" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selesai *</label>
                        <input type="time" name="jam_selesai" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Dosen & Korti -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen</label>
                        <input type="text" name="dosen" id="dosen_input" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Nama Dosen">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Korti/PJ</label>
                        <input type="text" name="korti" id="korti_input" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Nama PJ">
                    </div>
                </div>

                <!-- Ruangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                    <input type="text" name="ruangan" class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Gedung A R.304 atau Zoom">
                </div>

                <!-- Buttons -->
                <div class="flex gap-3 pt-2">
                    <a href="<?= base_url('jadwal') ?>" class="flex-1 py-2.5 text-center text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
    const selectMk = document.getElementById('mata_kuliah_id');
    const selectSemester = document.getElementById('semester_filter');
    const inputDosen = document.getElementById('dosen_input');
    const inputKorti = document.getElementById('korti_input');
    const activeSemesterId = <?= $activeSemesterId ? "'{$activeSemesterId}'" : 'null' ?>;
    const scheduledIds = <?= json_encode(array_values($scheduledMatkulIds)) ?>;

    if (selectMk) {
        const allMkOptions = Array.from(selectMk.querySelectorAll('option'));

        // Filter by semester (only when not locked)
        if (selectSemester && !activeSemesterId) {
            selectSemester.addEventListener('change', function() {
                const semesterId = this.value;
                selectMk.innerHTML = '<option value="">Pilih Mata Kuliah</option>';

                allMkOptions.forEach(opt => {
                    if (opt.value === "") return;
                    const mkSemId = opt.getAttribute('data-semester');
                    const isScheduled = scheduledIds.includes(opt.value);

                    if (semesterId === 'all' || mkSemId === semesterId) {
                        const newOpt = opt.cloneNode(true);
                        if (isScheduled) {
                            newOpt.disabled = true;
                            newOpt.textContent = opt.textContent.replace(' (sudah dijadwalkan)', '') + ' (sudah dijadwalkan)';
                        }
                        selectMk.appendChild(newOpt);
                    }
                });

                inputDosen.value = '';
                inputKorti.value = '';
            });
        }

        // Auto-fill dosen/korti
        selectMk.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || selectedOption.value === "") {
                inputDosen.value = '';
                inputKorti.value = '';
                return;
            }

            const dosen = selectedOption.getAttribute('data-dosen');
            const korti = selectedOption.getAttribute('data-korti');

            if (dosen) inputDosen.value = dosen;
            if (korti) inputKorti.value = korti;
        });
    }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>