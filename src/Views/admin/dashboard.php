<?php

/**
 * Admin Dashboard
 */
$title = 'Dashboard Admin';
$pageTitle = 'Dashboard';
ob_start();
?>

<div class="space-y-6">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-users text-primary-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['mahasiswa'] ?></p>
                    <p class="text-sm text-gray-500">Mahasiswa</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Mahasiswa -->
    <div class="card">
        <div class="p-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">Mahasiswa Terbaru</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Angkatan</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMahasiswa as $mhs): ?>
                        <tr>
                            <td class="font-mono"><?= $mhs['nim'] ?? '-' ?></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <img src="<?= ($mhs['foto'] ?? null) ?: 'https://ui-avatars.com/api/?name=' . urlencode($mhs['nama'] ?? 'User') ?>"
                                        class="w-8 h-8 rounded-full" alt="">
                                    <?= $mhs['nama'] ?? '-' ?>
                                </div>
                            </td>
                            <td class="text-gray-500"><?= $mhs['email'] ?? '-' ?></td>
                            <td><?= $mhs['angkatan'] ?? '-' ?></td>
                            <td class="text-gray-500"><?= isset($mhs['created_at']) ? formatDate($mhs['created_at']) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/admin/layout.php';
?>