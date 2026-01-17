<?php

/**
 * Halaman Konfirmasi Password untuk User Google Baru
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password - SIAPGRAK</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .login-bg {
            background: linear-gradient(135deg, #e7d4d4 0%, #e8d6d6 50%, #ddc8c8 100%);
        }

        .login-bg-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 font-inter">
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Left Section: Branding (Desktop Only) -->
        <div class="hidden lg:flex lg:w-1/2 login-bg-blue relative overflow-hidden flex-col justify-center items-start p-12 lg:p-16">
            <div class="relative z-10 max-w-xl">
                <!-- Welcome Text with white left border -->
                <div class="border-l-4 border-white pl-6 mb-6">
                    <h1 class="text-3xl lg:text-4xl font-bold text-white leading-tight">
                        Selamat Datang di
                    </h1>
                </div>

                <h2 class="text-2xl lg:text-3xl font-bold text-white mb-8">
                    SIAPGRAK - Mahasiswa Politala
                </h2>

                <p class="text-base text-blue-100 leading-relaxed max-w-md">
                    Platform manajemen kuliah yang dirancang untuk membantu mahasiswa Politeknik Negeri Tanah Laut mengorganisir jadwal, tugas, dan materi kuliah dengan efisien dan terintegrasi.
                </p>
            </div>
        </div>

        <!-- Right Section: Password Confirmation Form -->
        <div class="flex-1 flex items-center justify-center p-6 lg:p-12 bg-white">
            <div class="w-full max-w-md">

                <!-- Mobile Header -->
                <div class="lg:hidden mb-8 text-center">
                    <div class="login-bg-blue p-6 rounded-xl mb-6">
                        <p class="text-sm text-blue-100 leading-relaxed">
                            Platform manajemen kuliah yang dirancang untuk membantu mahasiswa Politeknik Negeri Tanah Laut mengorganisir jadwal, tugas, dan materi kuliah dengan efisien.
                        </p>
                    </div>
                </div>

                <!-- User Info Card -->
                <div class="mb-6 p-4 bg-gray-50 rounded-xl flex items-center gap-4">
                    <?php if (!empty($pending['foto'])): ?>
                        <img src="<?= $pending['foto'] ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                            <i class="fas fa-user text-primary-600"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($pending['nama']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($pending['email']) ?></p>
                    </div>
                </div>

                <!-- Form Header -->
                <div class="mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">Buat Password</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Akun Anda belum memiliki password. Silakan buat password untuk melanjutkan.
                    </p>
                </div>

                <!-- Error Message -->
                <?php if ($error = flash('error')): ?>
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                        <div class="flex-shrink-0 text-red-500 mt-0.5">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Terjadi Kesalahan</h3>
                            <p class="text-sm text-red-600 mt-1"><?= $error ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Password Form -->
                <form action="<?= base_url('confirm-password') ?>" method="POST" class="space-y-5">
                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Minimal 6 karakter"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-gray-900 placeholder-gray-400 pr-12"
                                required
                                minlength="6">
                            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors text-gray-900 placeholder-gray-400 pr-12"
                                required
                                minlength="6">
                            <button type="button" onclick="togglePassword('password_confirmation', 'toggleIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Simpan & Lanjutkan
                    </button>
                </form>

                <!-- Cancel Link -->
                <div class="mt-6 text-center">
                    <a href="<?= base_url('login') ?>" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Login
                    </a>
                </div>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-400">
                        &copy; <?= date('Y') ?> SIAPGRAK v1.0 - Politeknik Negeri Tanah Laut
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>