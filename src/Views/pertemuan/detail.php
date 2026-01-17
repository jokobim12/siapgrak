<?php

/**
 * Pertemuan Detail View
 * Dengan upload materi dan daftar tugas
 */
$title = "Pertemuan {$pertemuan['nomor_pertemuan']} - {$pertemuan['nama_mk']}";
$pageTitle = "P{$pertemuan['nomor_pertemuan']} - {$pertemuan['nama_mk']}";
ob_start();
?>

<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500">
        <a href="<?= base_url('mata-kuliah') ?>" class="hover:text-primary-600">Mata Kuliah</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="<?= base_url('mata-kuliah/detail?id=' . $pertemuan['mata_kuliah_id']) ?>" class="hover:text-primary-600">
            <?= $pertemuan['nama_mk'] ?>
        </a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900">Pertemuan <?= $pertemuan['nomor_pertemuan'] ?></span>
    </nav>

    <!-- Header -->
    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="badge-primary mb-2">Pertemuan <?= $pertemuan['nomor_pertemuan'] ?></span>
                <h1 class="text-2xl font-bold text-gray-900"><?= $pertemuan['judul'] ?: "Pertemuan {$pertemuan['nomor_pertemuan']}" ?></h1>
                <p class="text-gray-500 mt-1"><?= $pertemuan['nama_mk'] ?> (<?= $pertemuan['kode_mk'] ?>)</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openUploadModal()" class="btn-primary">
                    <i class="fas fa-upload mr-2"></i>
                    Upload Materi
                </button>
                <button onclick="openTugasModal()" class="btn-outline">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Tugas
                </button>
            </div>
        </div>

        <?php if ($pertemuan['deskripsi']): ?>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-600"><?= nl2br($pertemuan['deskripsi']) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Materi Section -->
        <div class="card">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">
                    <i class="fas fa-file-alt text-primary-500 mr-2"></i>
                    Materi (<?= count($materiList) ?>)
                </h2>
            </div>

            <div class="divide-y divide-gray-100">
                <?php if (!empty($materiList)): ?>
                    <?php foreach ($materiList as $materi): ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <i class="fas <?= getFileIcon($materi['tipe_file']) ?>"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 truncate"><?= $materi['judul'] ?></h4>
                                    <p class="text-sm text-gray-500"><?= $materi['nama_file'] ?></p>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                                        <span><?= $materi['uploader_nama'] ?></span>
                                        <span><?= formatDateTime($materi['created_at']) ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="<?= $materi['file_gdrive_url'] ?>" target="_blank"
                                        class="p-2 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php if ($materi['uploaded_by'] == auth()['id']): ?>
                                        <button onclick="deleteMateri(<?= $materi['id'] ?>)"
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada materi</p>
                        <button onclick="openUploadModal()" class="btn-primary mt-4">
                            <i class="fas fa-upload mr-2"></i>
                            Upload Materi Pertama
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tugas Section -->
        <div class="card">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">
                    <i class="fas fa-tasks text-amber-500 mr-2"></i>
                    Tugas (<?= count($tugasList) ?>)
                </h2>
            </div>

            <div class="divide-y divide-gray-100">
                <?php if (!empty($tugasList)): ?>
                    <?php foreach ($tugasList as $tugas): ?>
                        <a href="<?= base_url('tugas/detail?id=' . $tugas['id']) ?>"
                            class="block p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-medium text-gray-900"><?= $tugas['judul'] ?></h4>
                                        <?php if ($tugas['sudah_submit']): ?>
                                            <span class="badge-success">Sudah</span>
                                        <?php elseif ($tugas['sisa_hari'] < 0): ?>
                                            <span class="badge-danger">Terlambat</span>
                                        <?php elseif ($tugas['sisa_hari'] <= 1): ?>
                                            <span class="badge-warning">Segera!</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= $tugas['deskripsi'] ?></p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-medium <?= $tugas['sisa_hari'] < 0 ? 'text-red-600' : 'text-gray-900' ?>">
                                        <?= formatDate($tugas['deadline']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500"><?= date('H:i', strtotime($tugas['deadline'])) ?> WITA</p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
                        <p>Belum ada tugas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden">
    <div class="modal-overlay" onclick="closeUploadModal()"></div>
    <div class="modal p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Upload Materi</h3>
            <button onclick="closeUploadModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <form action="<?= base_url('pertemuan/upload') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="pertemuan_id" value="<?= $pertemuan['id'] ?>">

            <div class="space-y-4">
                <div>
                    <label class="label">Judul Materi</label>
                    <input type="text" name="judul" class="input" placeholder="Contoh: Slide Pertemuan 1" required>
                </div>

                <div>
                    <label class="label">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" class="input" rows="3" placeholder="Deskripsi singkat tentang materi ini..."></textarea>
                </div>

                <div>
                    <label class="label">File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors cursor-pointer"
                        onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-600">Klik untuk memilih file</p>
                        <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, PowerPoint, Gambar, Video</p>
                        <p id="selectedFileName" class="text-sm text-primary-600 font-medium mt-2 hidden"></p>
                    </div>
                    <input type="file" name="file" id="fileInput" class="hidden" required
                        onchange="showFileName(this)">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeUploadModal()" class="btn-outline flex-1">Batal</button>
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-upload mr-2"></i>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tugas Modal -->
<div id="tugasModal" class="hidden">
    <div class="modal-overlay" onclick="closeTugasModal()"></div>
    <div class="modal p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Tambah Tugas</h3>
            <button onclick="closeTugasModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <form action="<?= base_url('tugas/create') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="pertemuan_id" value="<?= $pertemuan['id'] ?>">

            <div class="space-y-4">
                <div>
                    <label class="label">Judul Tugas</label>
                    <input type="text" name="judul" class="input" placeholder="Contoh: Tugas Praktikum 1" required>
                </div>

                <div>
                    <label class="label">Deskripsi</label>
                    <textarea name="deskripsi" class="input" rows="3" placeholder="Deskripsi tugas..."></textarea>
                </div>

                <div>
                    <label class="label">Deadline</label>
                    <input type="datetime-local" name="deadline" class="input" required>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeTugasModal()" class="btn-outline flex-1">Batal</button>
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() {
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function openTugasModal() {
        document.getElementById('tugasModal').classList.remove('hidden');
    }

    function closeTugasModal() {
        document.getElementById('tugasModal').classList.add('hidden');
    }

    function showFileName(input) {
        const fileName = document.getElementById('selectedFileName');
        if (input.files.length > 0) {
            fileName.textContent = input.files[0].name;
            fileName.classList.remove('hidden');
        }
    }

    function deleteMateri(id) {
        if (confirm('Yakin ingin menghapus materi ini?')) {
            fetch('<?= base_url('pertemuan/delete-materi') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
                else alert(data.error);
            });
        }
    }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>