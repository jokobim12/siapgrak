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
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.5s ease-out'
                    },
                    keyframes: {
                        blob: {
                            '0%': {
                                transform: 'translate(0px, 0px) scale(1)'
                            },
                            '33%': {
                                transform: 'translate(30px, -50px) scale(1.1)'
                            },
                            '66%': {
                                transform: 'translate(-20px, 20px) scale(0.9)'
                            },
                            '100%': {
                                transform: 'translate(0px, 0px) scale(1)'
                            },
                        },
                        fadeInUp: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(10px)'
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)'
                            },
                        }
                    }
                }
            }
        }
    </script>
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="min-h-screen bg-white font-inter flex flex-col lg:flex-row">

    <!-- Left Section: Hero / Branding -->
    <div class="lg:w-1/2 bg-primary-600 relative overflow-hidden flex flex-col justify-center items-center text-white p-12">
        <!-- Abstract Pattern Background -->
        <div class="absolute inset-0 z-0 opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
            </svg>
        </div>

        <!-- Decoration Circles -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-primary-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-80 h-80 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>

        <!-- Content -->
        <div class="relative z-10 text-center lg:text-left max-w-lg">
            <div class="inline-flex items-center justify-center p-3 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl mb-8 shadow-2xl">
                <i class="fas fa-graduation-cap text-4xl"></i>
            </div>
            <h1 class="text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                Kelola Kuliahmu, <br> Raih Prestasimu.
            </h1>
            <p class="text-lg text-blue-100 mb-8 leading-relaxed">
                SIAPGRAK membantu mahasiswa Politala mengorganisir jadwal, tugas, dan materi kuliah dalam satu platform yang terintegrasi.
            </p>

            <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-lg backdrop-blur-sm border border-white/10">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="text-sm font-medium">Auto-Sync GDrive</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-lg backdrop-blur-sm border border-white/10">
                    <i class="fas fa-check-circle text-green-400"></i>
                    <span class="text-sm font-medium">WhatsApp Reminder</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="lg:w-1/2 flex items-center justify-center p-8 bg-gray-50 lg:bg-white relative">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center lg:text-left">
                <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Selamat Datang 👋</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Silakan masuk menggunakan akun institusi Anda.
                </p>
            </div>

            <?php if ($error = flash('error')): ?>
                <div class="p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3 animate-fade-in-up">
                    <div class="flex-shrink-0 text-red-500 mt-0.5">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Gagal Masuk</h3>
                        <p class="text-sm text-red-600 mt-1"><?= $error ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-8 space-y-6">
                <!-- Google Login Button with enhanced styling -->
                <div class="relative group">
                    <div class="absolute -inset-0.5  rounded-xl opacity-30 group-hover:opacity-70 transition duration-200 blur"></div>
                    <a href="<?= $authUrl ?>" class="relative flex items-center justify-center gap-4 w-full px-8 py-4 bg-white border border-gray-100 rounded-lg">
                        <svg class="w-6 h-6" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        <span class="text-gray-700 font-semibold text-base">Masuk dengan Google</span>
                    </a>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                </div>

                <div class="pt-2 text-center">
                    <p class="text-xs text-gray-400">
                        Dengan masuk, Anda menyetujui Kebijakan Privasi & Syarat Penggunaan<br>
                        Hanya untuk Mahasiswa <span class="text-indigo-600 font-medium">Politeknik Negeri Tanah Laut</span>
                    </p>
                </div>
            </div>

            <div class="mt-auto pt-10 text-center lg:text-left">
                <p class="text-xs text-gray-400">&copy; <?= date('Y') ?> SIAPGRAK v2.0</p>
            </div>
        </div>
    </div>

    <!-- Custom Animation Style -->
    <style>
        @keyframes blob {
            0% {
                transform: translate(0px, 0px) scale(1);
            }

            33% {
                transform: translate(30px, -50px) scale(1.1);
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }

            100% {
                transform: translate(0px, 0px) scale(1);
            }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>

</body>

</html>