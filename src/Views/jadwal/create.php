<?php
$title = 'Tambah Jadwal';
ob_start();
?>

<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Jadwal Baru</h1>
            <p class="text-gray-500 text-sm">Masukan detail jadwal mata kuliah Anda</p>
        </div>
        <a href="<?= base_url('jadwal') ?>" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
        </a>
    </div>

    <!-- Alert jika MK kosong -->
    <?php if (empty($matkul)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Anda belum memiliki Mata Kuliah.
                        <a href="<?= base_url('mata-kuliah/tambah') ?>" class="font-medium underline hover:text-yellow-600">Buat Mata Kuliah dulu</a>
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>

        <form action="<?= base_url('jadwal/simpan') ?>" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            <?= csrf_field() ?>

            <!-- Semester Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter Semester</label>
                <select id="semester_filter" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="all">-- Semua Semester --</option>
                    <?php if (!empty($semesters)): ?>
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= $sem['id'] ?>"><?= $sem['nama'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih semester untuk menyaring daftar mata kuliah.</p>
            </div>

            <!-- Mata Kuliah Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah</label>
                <select name="mata_kuliah_id" id="mata_kuliah_id" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required onchange="updateDetails(this)">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php foreach ($matkul as $mk): ?>
                        <option value="<?= $mk['id'] ?>"
                            data-dosen="<?= $mk['dosen'] ?? '' ?>"
                            data-korti="<?= $mk['korti'] ?? '' ?>"
                            data-semester="<?= $mk['semester_id'] ?>">
                            <?= $mk['nama_mk'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">Pilih mata kuliah dari daftar yang sudah Anda buat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Hari -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
                    <select name="hari" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                        <option value="senin">Senin</option>
                        <option value="selasa">Selasa</option>
                        <option value="rabu">Rabu</option>
                        <option value="kamis">Kamis</option>
                        <option value="jumat">Jumat</option>
                        <option value="sabtu">Sabtu</option>
                    </select>
                </div>

                <!-- Jam -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                        <input type="time" name="jam_mulai" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                        <input type="time" name="jam_selesai" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dosen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Pengampu</label>
                    <input type="text" name="dosen" id="dosen_input" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nama Dosen">
                    <p class="text-xs text-gray-500 mt-1">*Akan mengupdate data Mata Kuliah</p>
                </div>

                <!-- Korti -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Korti / PJ Matkul</label>
                    <input type="text" name="korti" id="korti_input" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nama Ketua Kelas">
                    <p class="text-xs text-gray-500 mt-1">*Ketua kelas untuk matkul ini</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan / Tempat</label>
                <input type="text" name="ruangan" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Contoh: Gedung A R.304 atau Zoom Meeting">
            </div>


            <div class="pt-4 flex justify-end gap-3">
                <a href="<?= base_url('jadwal') ?>" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">Batal</a>
                <button type="submit" class="btn-primary px-6 py-2 rounded-lg">Simpan Jadwal</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    const selectMk = document.getElementById('mata_kuliah_id');
    const selectSemester = document.getElementById('semester_filter');
    const inputDosen = document.getElementById('dosen_input');
    const inputKorti = document.getElementById('korti_input');

    // Store original options
    const allMkOptions = Array.from(selectMk.options);

    // Filter Logic
    if (selectSemester) {
        selectSemester.addEventListener('change', function() {
            const semesterId = this.value;

            // Clear current options (keep first placeholder)
            selectMk.innerHTML = '<option value="">-- Pilih Mata Kuliah --</option>';

            allMkOptions.forEach(opt => {
                if (opt.value === "") return; // Skip placeholder

                const mkSemId = opt.getAttribute('data-semester');
                if (semesterId === 'all' || mkSemId === semesterId) {
                    selectMk.appendChild(opt); // Re-append valid options
                }
            });

            // Reset inputs
            inputDosen.value = '';
            inputKorti.value = '';
        });
    }

    // Existing update details logic
    selectMk.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        // Check if selection is valid
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
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>