<?php

/**
 * Google OAuth Helper
 * Mengelola autentikasi dengan Google
 */

namespace App\Helpers;

class GoogleAuthHelper
{
    private $client;

    public function __construct()
    {
        $this->client = new \Google\Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);

        // Scopes yang diperlukan
        $this->client->addScope('email');
        $this->client->addScope('profile');
        $this->client->addScope(\Google\Service\Drive::DRIVE_FILE);
        $this->client->addScope(\Google\Service\Drive::DRIVE);

        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        // Network Tweak: Force IPv4 and Disable Verify (Dev Only)
        $this->client->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false, // Bypass SSL (Dev Only)
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4 // Force IPv4
            ]
        ]));
    }

    /**
     * Get Google OAuth URL
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange code untuk access token
     */
    public function getAccessToken($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return [
                    'success' => false,
                    'error' => $token['error_description'] ?? $token['error']
                ];
            }

            $this->client->setAccessToken($token);

            return [
                'success' => true,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_in' => $token['expires_in'],
                'token' => $token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get user info dari Google
     */
    public function getUserInfo($accessToken)
    {
        try {
            $this->client->setAccessToken($accessToken);

            $oauth2 = new \Google\Service\Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();

            return [
                'success' => true,
                'user' => [
                    'google_id' => $userInfo->getId(),
                    'email' => $userInfo->getEmail(),
                    'nama' => $userInfo->getName(),
                    'foto' => $userInfo->getPicture(),
                    'verified' => $userInfo->getVerifiedEmail()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken($refreshToken)
    {
        try {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            $token = $this->client->getAccessToken();

            return [
                'success' => true,
                'access_token' => $token['access_token'],
                'expires_in' => $token['expires_in'],
                'token' => $token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validasi email mahasiswa Politala
     * Format: xxx@mhs.politala.ac.id (nama atau NIM)
     */
    public function validateMahasiswaEmail($email, $nama = '')
    {
        // Cek apakah email berakhiran @mhs.politala.ac.id
        if (!preg_match('/@mhs\.politala\.ac\.id$/i', $email)) {
            return [
                'valid' => false,
                'nim' => null
            ];
        }

        // Coba ekstrak NIM dari awal email (jika format 2401301001@mhs.politala.ac.id)
        if (preg_match('/^(\d{10})/', $email, $matches)) {
            return [
                'valid' => true,
                'nim' => $matches[1]
            ];
        }

        // Coba ekstrak NIM dari nama profil Google (format: "2401301001 Joko Bimantaro")
        if (preg_match('/^(\d{10})\s/', $nama, $matches)) {
            return [
                'valid' => true,
                'nim' => $matches[1]
            ];
        }

        // Coba ekstrak NIM dari nama profil Google (format: "2401301001Joko..." tanpa spasi)
        if (preg_match('/^(\d{10})/', $nama, $matches)) {
            return [
                'valid' => true,
                'nim' => $matches[1]
            ];
        }

        // Email valid tapi NIM tidak ditemukan - akan diminta input manual
        return [
            'valid' => true,
            'nim' => null,
            'need_nim' => true
        ];
    }

    /**
     * Extract NIM dan info dari email
     */
    public function extractStudentInfo($email, $nama)
    {
        $validation = $this->validateMahasiswaEmail($email, $nama);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'Email bukan email mahasiswa Politala yang valid'
            ];
        }

        $nim = $validation['nim'];

        // Jika NIM tidak ditemukan, coba ekstrak dari nama lagi dengan berbagai format
        if (!$nim) {
            // Format: "2401301001 Joko Bimantaro" atau "2401301001Joko Bimantaro"
            if (preg_match('/(\d{10})/', $nama, $matches)) {
                $nim = $matches[1];
            }
        }

        // Jika masih tidak ketemu, minta input manual (untuk sementara pakai placeholder)
        if (!$nim) {
            return [
                'success' => false,
                'error' => 'NIM tidak dapat diekstrak dari profil. Silakan hubungi admin untuk registrasi manual.',
                'need_nim' => true,
                'email' => $email,
                'nama' => $nama
            ];
        }

        $angkatan = 2000 + intval(substr($nim, 0, 2));
        $semester = hitungSemester($nim);

        // Hapus NIM dari nama jika ada
        $namaBersih = preg_replace('/^\d{10}\s*/', '', $nama);
        if (empty(trim($namaBersih))) {
            $namaBersih = $nama;
        }

        return [
            'success' => true,
            'nim' => $nim,
            'nama' => $namaBersih,
            'email' => $email,
            'angkatan' => $angkatan,
            'semester' => $semester
        ];
    }
}
