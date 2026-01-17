<?php

/**
 * 404 Error Page
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | SIAPGRAK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="text-center">
        <div class="text-9xl font-bold gradient-text mb-4">404</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Halaman Tidak Ditemukan</h1>
        <p class="text-gray-500 mb-8">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="<?= base_url('dashboard') ?>" class="btn-primary">
            <i class="fas fa-home mr-2"></i>
            Kembali ke Dashboard
        </a>
    </div>
</body>

</html>