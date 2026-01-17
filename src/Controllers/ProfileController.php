<?php

namespace App\Controllers;

require_once ROOT_PATH . '/src/Helpers/GoogleDriveHelper.php';
require_once ROOT_PATH . '/src/Controllers/AuthController.php';

use App\Helpers\GoogleDriveHelper;

class ProfileController
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function index()
    {
        $user = auth();
        if (!$user) {
            redirect('login');
        }

        // Get fresh user data
        $mahasiswa = $this->db->findById('mahasiswa', $user['id']);

        view('profile.index', ['user' => $mahasiswa]);
    }

    public function update()
    {
        $user = auth();
        if (!$user) {
            redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('profile');
        }

        $nama = sanitize($_POST['nama'] ?? '');
        $kelas = sanitize($_POST['kelas'] ?? '');
        $semester = intval($_POST['semester'] ?? 1);
        $no_hp = sanitize($_POST['no_hp'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $foto_style = $_POST['foto_style'] ?? '';

        // Validation
        if (empty($nama)) {
            flash('error', 'Nama harus diisi');
            redirect('profile');
        }

        $updateData = [
            'nama' => $nama,
            'kelas' => $kelas,
            'semester_aktif' => $semester,
            'no_hp' => $no_hp,
            'bio' => $bio,
            'foto_style' => $foto_style
        ];

        // Handle Photo Upload
        if (!empty($_POST['foto_base64'])) {
            $data = $_POST['foto_base64'];
            $image_array_1 = explode(";", $data);
            $image_array_2 = explode(",", $image_array_1[1]);
            $data = base64_decode($image_array_2[1]);

            $uploadDir = PUBLIC_PATH . '/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'profile_' . $user['id'] . '_' . time() . '.jpg';
            $destination = $uploadDir . $filename;

            if (file_put_contents($destination, $data)) {
                $updateData['foto'] = base_url('uploads/profiles/' . $filename);
                // Reset foto_style to center since we cropped it
                $updateData['foto_style'] = 'center center';
            } else {
                flash('error', 'Gagal menyimpan foto hasil crop');
                redirect('profile');
            }
        } elseif (!empty($_FILES['foto']['name'])) {
            // ... (keep fallback for normal upload without crop if needed, but crop is preferred)
            // Since we use crop, this part is less likely to be triggered unless JS fails
            $file = $_FILES['foto'];

            // Validate image
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($file['type'], $allowedTypes)) {
                flash('error', 'Hanya file JPG/PNG yang diperbolehkan');
                redirect('profile');
            }

            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                flash('error', 'Ukuran foto maksimal 2MB');
                redirect('profile');
            }
            // ... (rest of normal upload logic)
            // For brevity, let's just keep the original logic as fallback or remove if we want to force crop
            // Let's keep it but prioritized base64

            $uploadDir = PUBLIC_PATH . '/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $updateData['foto'] = base_url('uploads/profiles/' . $filename);
            } else {
                flash('error', 'Gagal upload foto');
                redirect('profile');
            }
        }

        $this->db->update('mahasiswa', $user['id'], $updateData);

        // Update session
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['semester_aktif'] = $semester;
        if (isset($updateData['foto'])) {
            $_SESSION['user']['foto'] = $updateData['foto'];
        }

        flash('success', 'Profil berhasil diperbarui');
        redirect('profile');
    }
}
