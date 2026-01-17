<?php
$title = 'Profil Saya';
ob_start();

$user = $user ?? auth();
?>

<div class="space-y-4 pb-20 lg:pb-0 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="<?= base_url('dashboard') ?>" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Profil Saya</h1>
    </div>

    <form action="<?= base_url('profile/update') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Profile Card -->
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <!-- Avatar Section -->
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row items-center gap-4">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg overflow-hidden bg-gray-200">
                        <?php
                        $foto = $user['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&background=3b82f6&color=fff';
                        $fotoStyle = $user['foto_style'] ?? 'center center';
                        ?>
                        <img src="<?= $foto ?>" alt="Profile" class="w-full h-full object-cover" style="object-position: <?= $fotoStyle ?>;">
                    </div>
                    <label for="foto-upload" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <i class="fas fa-camera text-white text-lg"></i>
                    </label>
                    <input type="file" id="foto-upload" name="foto" class="hidden" accept="image/*" onchange="onFileSelect(this)">
                </div>

                <!-- Name & Email -->
                <div class="text-center sm:text-left">
                    <h2 class="text-lg font-bold text-gray-900"><?= $user['nama'] ?></h2>
                    <p class="text-sm text-gray-500"><?= $user['email'] ?></p>
                    <p class="text-xs text-gray-400 mt-1">NIM: <?= $user['nim'] ?></p>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="p-6 space-y-4">
                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= $user['nama'] ?>"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <!-- Semester & Kelas -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <input type="number" name="semester" value="<?= $user['semester_aktif'] ?>" min="1" max="14"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <input type="text" name="kelas" value="<?= $user['kelas'] ?? '' ?>" placeholder="TI-1A"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- WhatsApp -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                            <i class="fab fa-whatsapp text-green-500"></i>
                        </span>
                        <input type="text" name="no_hp" value="<?= $user['no_hp'] ?? '' ?>" placeholder="6281234567890"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-9">
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="3" placeholder="Tulis sedikit tentang diri Anda..."
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"><?= $user['bio'] ?? '' ?></textarea>
                </div>

                <!-- Read-only Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 font-medium mb-2">Informasi Akun (Tidak dapat diubah)</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <span class="text-gray-700 ml-1"><?= $user['email'] ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">NIM:</span>
                            <span class="text-gray-700 ml-1"><?= $user['nim'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Crop Modal -->
<div id="cropModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeCropModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">Sesuaikan Foto</h3>
            <button onclick="closeCropModal()" class="p-1 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4">
            <div class="w-full h-64 bg-gray-100 rounded-lg overflow-hidden">
                <img id="image-to-crop" class="max-w-full h-auto" src="" alt="Crop Preview">
            </div>
        </div>
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex gap-3">
            <button type="button" onclick="closeCropModal()" class="flex-1 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Batal
            </button>
            <button type="button" onclick="cropImage()" class="flex-1 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Terapkan
            </button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    let cropper;
    const image = document.getElementById('image-to-crop');
    const input = document.getElementById('foto-upload');
    const modal = document.getElementById('cropModal');
    const previewImg = document.querySelector('img[alt="Profile"]');
    const croppedInput = document.createElement('input');
    croppedInput.type = 'hidden';
    croppedInput.name = 'foto_base64';
    document.querySelector('form').appendChild(croppedInput);

    function onFileSelect(e) {
        if (e.files && e.files[0]) {
            const file = e.files[0];
            const reader = new FileReader();
            reader.onload = function(evt) {
                image.src = evt.target.result;
                openCropModal();
            };
            reader.readAsDataURL(file);
        }
    }

    function openCropModal() {
        modal.classList.remove('hidden');
        if (cropper) cropper.destroy();
        cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 0.8,
            dragMode: 'move'
        });
    }

    function closeCropModal() {
        modal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        input.value = '';
    }

    function cropImage() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });
        previewImg.src = canvas.toDataURL('image/jpeg');
        previewImg.style.objectPosition = 'center center';
        croppedInput.value = canvas.toDataURL('image/jpeg');
        closeCropModal();
    }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>