<?php

/**
 * Admin Login Page
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SIAPGRAK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl mb-4">
                <i class="fas fa-user-shield text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
            <p class="text-gray-400">SIAPGRAK - Sistem Organisasi Materi Kuliah</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <?php if ($error = flash('error')): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="label">Username</label>
                        <input type="text" name="username" class="input" placeholder="admin" required>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input type="password" name="password" class="input" placeholder="********" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary w-full mt-6">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?= base_url('login') ?>" class="text-sm text-primary-600 hover:text-primary-700">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali ke Login Mahasiswa
                </a>
            </div>
        </div>
    </div>
</body>

</html>