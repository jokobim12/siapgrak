<?php

/**
 * Halaman Input NIM Manual
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input NIM - SIAPGRAK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="min-h-screen bg-gradient-to-br from-primary-600 via-primary-700 to-secondary-700">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-xl mb-4">
                    <i class="fas fa-graduation-cap text-4xl text-primary-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-white">SIAPGRAK</h1>
                <p class="text-white/80 mt-2">Lengkapi Data Anda</p>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <!-- User Info -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl mb-6">
                    <img src="<?= $pending['foto'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($pending['nama']) ?>"
                        alt="Foto" class="w-14 h-14 rounded-full object-cover">
                    <div>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($pending['nama']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($pending['email']) ?></p>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        <p class="text-sm text-amber-800">
                            NIM tidak dapat diekstrak otomatis dari profil Google Anda. Silakan masukkan NIM Anda secara manual.
                        </p>
                    </div>
                </div>

                <?php if ($error = flash('error')): ?>
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-6">
                        <label class="label">Nomor Induk Mahasiswa (NIM)</label>
                        <input type="text"
                            name="nim"
                            class="input text-center text-xl tracking-widest font-mono"
                            placeholder="2401301001"
                            pattern="\d{10}"
                            maxlength="10"
                            required
                            autofocus>
                        <p class="text-xs text-gray-500 mt-2 text-center">Masukkan 10 digit NIM Anda</p>
                    </div>

                    <div class="mb-6">
                        <label class="label">Nomor HP (WhatsApp)</label>
                        <input type="text"
                            name="no_hp"
                            class="input text-center text-xl tracking-widest font-mono"
                            placeholder="628123456789"
                            pattern="62\d+"
                            required>
                        <p class="text-xs text-gray-500 mt-2 text-center">Format: 628... (Gunakan kode negara 62)</p>
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-check mr-2"></i>
                        Lanjutkan
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="<?= base_url('login') ?>" class="text-sm text-gray-500 hover:text-primary-600">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>