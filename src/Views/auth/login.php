<?php
/**
 * Halaman Login
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAPGRAK</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center p-6 font-inter">

    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-xl bg-primary-600 text-white mb-4">
                <i class="fas fa-graduation-cap text-2xl"></i>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">SIAPGRAK</h1>
            <p class="text-sm text-gray-500 mt-1">
                Sistem Organisasi Materi Kuliah
            </p>
        </div>

        <!-- Card -->
        <div class="mt-10 bg-white border border-gray-200 rounded-xl p-8">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    Masuk ke akun Anda
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gunakan akun Google mahasiswa
                </p>
            </div>

            <?php if ($error = flash('error')): ?>
                <div class="mb-5 p-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg flex gap-2 items-center">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <!-- Google Login -->
            <a href="<?= $authUrl ?>"
               class="w-full flex items-center justify-center gap-3 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Masuk dengan Google
            </a>

            <p class="mt-4 text-xs text-gray-500">
                Hanya menerima email <strong>@mhs.politala.ac.id</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; <?= date('Y') ?> Politeknik Negeri Tanah Laut
        </div>
    </div>

</body>
</html>
