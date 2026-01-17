<?php
$title = 'Profil Saya';
ob_start();

$user = $user ?? auth();
?>

<div class="space-y-6 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
            <p class="text-gray-500 text-sm">Kelola informasi pribadi Anda</p>
        </div>
    </div>

    <form action="<?= base_url('profile/update') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>

        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-primary-600 to-indigo-600"></div>
            <div class="px-6 pb-6 relative">
                <!-- Avatar -->
                <div class="relative -mt-16 mb-4 inline-block">
                    <div class="w-32 h-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-200 group relative" style="width: 128px; height: 128px;">
                        <?php
                        $foto = $user['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&background=random';
                        $fotoStyle = $user['foto_style'] ?? 'center center';
                        ?>
                        <img src="<?= $foto ?>" alt="Profile" class="w-full h-full object-cover" style="object-position: <?= $fotoStyle ?>;">

                        <!-- Overlay Upload -->
                        <label for="foto-upload" class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <i class="fas fa-camera text-white text-2xl"></i>
                        </label>
                        <input type="file" id="foto-upload" name="foto" class="hidden" accept="image/*" onchange="onFileSelect(this)">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= $user['nama'] ?>" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="<?= $user['email'] ?>" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIM</label>
                            <input type="text" value="<?= $user['nim'] ?>" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                                <input type="number" name="semester" value="<?= $user['semester_aktif'] ?>" min="1" max="14" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                                <input type="text" name="kelas" value="<?= $user['kelas'] ?? '' ?>" placeholder="Contoh: TI-1A" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                            <input type="text" name="no_hp" value="<?= $user['no_hp'] ?? '' ?>" placeholder="628..." class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <!-- Posisi Foto dihapus karena sudah ada cropping -->
                    </div>
                </div>

                <!-- Bio -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Biodata / Tentang Saya</label>
                    <textarea name="bio" rows="4" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tulis sedikit tentang diri Anda..."><?= $user['bio'] ?? '' ?></textarea>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary px-6 py-2.5 rounded-lg flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>


<div id="cropModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Sesuaikan Foto</h3>
                        <div class="mt-4 w-full h-64 bg-gray-100 rounded-lg overflow-hidden relative">
                            <img id="image-to-crop" class="max-w-full h-auto" src="" alt="Crop Preview">
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="cropImage()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Terapkan
                </button>
                <button type="button" onclick="closeCropModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
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
    // Hidden input to store cropped base64
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

        // Destroy old cropper if exists
        if (cropper) {
            cropper.destroy();
        }

        // Init new cropper
        cropper = new Cropper(image, {
            aspectRatio: 1, // Square for profile
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
        // Reset input value so same file can be selected again if needed
        input.value = '';
    }

    function cropImage() {
        if (!cropper) return;

        // Get cropped canvas
        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300
        });

        // Set preview
        previewImg.src = canvas.toDataURL('image/jpeg');
        previewImg.style.objectPosition = 'center center'; // Reset position style since we cropped it

        // Set hidden input value
        croppedInput.value = canvas.toDataURL('image/jpeg');

        closeCropModal();
    }
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>