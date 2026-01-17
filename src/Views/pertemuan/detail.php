<?php

/**
 * Detail Pertemuan
 */
$title = 'P' . $pertemuan['nomor_pertemuan'] . ' - ' . $pertemuan['nama_mk'];
ob_start();
?>

<div class="space-y-4 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="<?= base_url('mata-kuliah/detail?id=' . $pertemuan['mata_kuliah_id']) ?>" class="p-2 -ml-2 text-gray-600 hover:text-gray-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-bold text-gray-800">Pertemuan <?= $pertemuan['nomor_pertemuan'] ?></h1>
            <p class="text-sm text-gray-500"><?= $pertemuan['nama_mk'] ?></p>
        </div>
    </div>

    <!-- Materi Section -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-file-alt text-primary-600 mr-2"></i>Materi (<?= count($materiList) ?>)
            </h2>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="btn-primary btn-sm">
                <i class="fas fa-upload mr-1"></i>Upload
            </button>
        </div>

        <?php if (!empty($materiList)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($materiList as $m): ?>
                    <div class="p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate"><?= $m['judul'] ?></p>
                            <p class="text-xs text-gray-500"><?= $m['nama_file'] ?> · <?= formatBytes($m['ukuran_file']) ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= $m['file_gdrive_url'] ?>" target="_blank" class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <form method="POST" action="<?= base_url('pertemuan/delete-materi') ?>" onsubmit="return confirm('Hapus materi ini?')">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state py-8">
                <i class="fas fa-folder-open empty-state-icon"></i>
                <p class="text-gray-500">Belum ada materi</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tugas Section -->
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">
                <i class="fas fa-tasks text-amber-600 mr-2"></i>Tugas (<?= count($tugasList) ?>)
            </h2>
            <button onclick="document.getElementById('tugasModal').classList.remove('hidden')" class="btn-secondary btn-sm">
                <i class="fas fa-plus mr-1"></i>Tambah
            </button>
        </div>

        <?php if (!empty($tugasList)): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($tugasList as $t): ?>
                    <div class="p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-tasks text-amber-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 truncate"><?= $t['judul'] ?></p>
                            <p class="text-xs text-gray-500"><?= $t['nama_file'] ?? 'File tidak tersedia' ?> · <?= isset($t['ukuran_file']) ? formatBytes($t['ukuran_file']) : '-' ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if (isset($t['file_gdrive_url'])): ?>
                                <a href="<?= $t['file_gdrive_url'] ?>" target="_blank" class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                            <form method="POST" action="<?= base_url('tugas/delete') ?>" onsubmit="return confirm('Hapus tugas ini?')">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state py-8">
                <i class="fas fa-clipboard empty-state-icon"></i>
                <p class="text-gray-500">Belum ada tugas</p>
            </div>
        <?php endif; ?>
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
            <!-- Progress Bar -->
            <div class="progress-container hidden px-6 pb-6">
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="progress-bar bg-primary-600 h-4 rounded-full text-xs font-medium text-blue-100 text-center p-0.5 leading-none transition-all duration-300 ease-out" style="width: 0%"> 0%</div>
                </div>
            </div>
        </form>
    </div>
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
        <form id="formTugas" method="POST" action="<?= base_url('tugas/create') ?>" enctype="multipart/form-data">
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
            <!-- Progress Bar -->
            <div class="progress-container hidden px-6 pb-6">
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="progress-bar bg-primary-600 h-4 rounded-full text-xs font-medium text-blue-100 text-center p-0.5 leading-none transition-all duration-300 ease-out" style="width: 0%"> 0%</div>
                </div>
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

            const progressBar = form.querySelector('.progress-bar');
            const progressContainer = form.querySelector('.progress-container');
            const submitBtn = form.querySelector('button[type="submit"]');

            // Reset state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = percentComplete + '%';

                    if (percentComplete === 100) {
                        submitBtn.innerHTML = '<i class="fas fa-cog fa-spin mr-2"></i>Memproses ke Google Drive...';
                    }
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    // Success
                    window.location.reload();
                } else {
                    // Error
                    alert('Upload gagal. Silakan coba lagi.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload';
                    progressContainer.classList.add('hidden');
                }
            });

            xhr.addEventListener('error', function() {
                alert('Terjadi kesalahan jaringan.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload';
                progressContainer.classList.add('hidden');
            });

            xhr.open('POST', form.action, true);
            xhr.send(formData);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleUpload('formMateri');
        handleUpload('formTugas');
    });
</script>