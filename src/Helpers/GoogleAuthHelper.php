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
     * Format: 2401301001@mhs.politala.ac.id
     */
    public function validateMahasiswaEmail($email)
    {
        // Pattern: 10 digit NIM + @mhs.politala.ac.id
        $pattern = '/^(\d{10}).*@mhs\.politala\.ac\.id$/i';

        if (preg_match($pattern, $email, $matches)) {
            return [
                'valid' => true,
                'nim' => $matches[1]
            ];
        }

        return [
            'valid' => false,
            'nim' => null
        ];
    }

    /**
     * Extract NIM dan info dari email
     */
    public function extractStudentInfo($email, $nama)
    {
        $validation = $this->validateMahasiswaEmail($email);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'Email bukan email mahasiswa Politala yang valid'
            ];
        }

        $nim = $validation['nim'];
        $angkatan = 2000 + intval(substr($nim, 0, 2));
        $semester = hitungSemester($nim);

        return [
            'success' => true,
            'nim' => $nim,
            'nama' => $nama,
            'email' => $email,
            'angkatan' => $angkatan,
            'semester' => $semester
        ];
    }
}
