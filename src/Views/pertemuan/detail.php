<?php

/**
 * Detail Pertemuan - Clean Design
 */
$title = 'P' . $pertemuan['nomor_pertemuan'] . ' - ' . $pertemuan['nama_mk'];
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <a href="<?= base_url('mata-kuliah/detail?id=' . $pertemuan['mata_kuliah_id']) ?>"
                class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg lg:text-xl font-bold text-gray-900">Pertemuan <?= $pertemuan['nomor_pertemuan'] ?></h1>
                <p class="text-xs text-gray-500 truncate"><?= $pertemuan['nama_mk'] ?></p>
            </div>
        </div>
    </div>

    <!-- Two Column Layout for Desktop -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Materi Section -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <h2 class="font-semibold text-gray-900 text-sm">
                    <i class="fas fa-file-alt text-blue-600 mr-2"></i>Materi
                    <span class="text-xs text-gray-400 font-normal ml-1">(<?= count($materiList) ?>)</span>
                </h2>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                    class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-upload mr-1"></i>Upload
                </button>
            </div>

            <?php if (!empty($materiList)): ?>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    <?php foreach ($materiList as $m): ?>
                        <div class="p-3 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file text-blue-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate"><?= $m['judul'] ?></p>
                                <p class="text-[11px] text-gray-500 truncate"><?= $m['nama_file'] ?> · <?= formatBytes($m['ukuran_file']) ?></p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <a href="<?= $m['file_gdrive_url'] ?>" target="_blank"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Buka file">
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                                <form id="deleteMateri<?= $m['id'] ?>" method="POST" action="<?= base_url('pertemuan/delete-materi') ?>">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="button" onclick="showConfirm('Hapus Materi', 'Apakah Anda yakin ingin menghapus materi ini?', '', true, 'deleteMateri<?= $m['id'] ?>')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-8 text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-folder-open text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">Belum ada materi</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tugas Section -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <h2 class="font-semibold text-gray-900 text-sm">
                    <i class="fas fa-tasks text-amber-600 mr-2"></i>Tugas
                    <span class="text-xs text-gray-400 font-normal ml-1">(<?= count($tugasList) ?>)</span>
                </h2>
                <button onclick="document.getElementById('tugasModal').classList.remove('hidden')"
                    class="px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-plus mr-1"></i>Tambah
                </button>
            </div>

            <?php if (!empty($tugasList)): ?>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    <?php foreach ($tugasList as $t): ?>
                        <div class="p-3 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tasks text-amber-600 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate"><?= $t['judul'] ?></p>
                                <p class="text-[11px] text-gray-500 truncate"><?= $t['nama_file'] ?? 'File tidak tersedia' ?></p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <?php if (isset($t['file_gdrive_url'])): ?>
                                    <a href="<?= $t['file_gdrive_url'] ?>" target="_blank"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Buka file">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                <?php endif; ?>
                                <form id="deleteTugasFile<?= $t['id'] ?>" method="POST" action="<?= base_url('tugas/delete') ?>">
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="button" onclick="showConfirm('Hapus Tugas', 'Apakah Anda yakin ingin menghapus tugas ini?', '', true, 'deleteTugasFile<?= $t['id'] ?>')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-8 text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-clipboard text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500">Belum ada tugas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50">
    <div class="modal-overlay" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="modal">
        <div class="modal-header">
            <h3 class="font-semibold text-gray-800">Upload Materi</h3>
            <button onclick="this.closest('#uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formMateri" method="POST" action="<?= base_url('pertemuan/upload') ?>" enctype="multipart/form-data">
            <div class="modal-body space-y-4">
                <input type="hidden" name="pertemuan_id" value="<?= $pertemuan['id'] ?>">
                <div>
                    <label class="label">Judul Materi</label>
                    <input type="text" name="judul" class="input" required>
                </div>
                <div>
                    <label class="label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" class="input" rows="2"></textarea>
                </div>
                <div>
                    <label class="label">File</label>
                    <input type="file" name="file" class="input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="this.closest('#uploadModal').classList.add('hidden')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-upload mr-2"></i>Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tugas Modal -->
<div id="tugasModal" class="hidden fixed inset-0 z-50">
    <div class="modal-overlay" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="modal">
        <div class="modal-header">
            <h3 class="font-semibold text-gray-800">Tambah Tugas</h3>
            <button onclick="this.closest('#tugasModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formTugas" method="POST" action="<?= base_url('tugas/upload') ?>" enctype="multipart/form-data">
            <div class="modal-body space-y-4">
                <input type="hidden" name="pertemuan_id" value="<?= $pertemuan['id'] ?>">
                <div>
                    <label class="label">Judul Tugas</label>
                    <input type="text" name="judul" class="input" required placeholder="Contoh: Laporan Akhir">
                </div>
                <div>
                    <label class="label">Deskripsi</label>
                    <textarea name="deskripsi" class="input" rows="3" placeholder="Deskripsi tugas..."></textarea>
                </div>
                <div>
                    <label class="label">File Tugas</label>
                    <input type="file" name="file" class="input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="this.closest('#tugasModal').classList.add('hidden')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-upload mr-2"></i>Upload
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>

<script>
    function handleUpload(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-cog fa-spin mr-2"></i>Memproses...';

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert('Gagal: ' + (response.error || 'Terjadi kesalahan'));
                            resetBtn();
                        }
                    } catch (e) {
                        window.location.reload();
                    }
                } else {
                    alert('Upload gagal. Status: ' + xhr.status);
                    resetBtn();
                }
            };

            function resetBtn() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload';
            }

            xhr.addEventListener('error', function() {
                alert('Terjadi kesalahan jaringan.');
                resetBtn();
            });

            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleUpload('formMateri');
        handleUpload('formTugas');
    });
</script>