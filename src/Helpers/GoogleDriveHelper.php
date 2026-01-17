<?php

/**
 * Google Drive Helper
 * Mengelola operasi file di Google Drive
 */

namespace App\Helpers;

require_once ROOT_PATH . '/config/config.php';

class GoogleDriveHelper
{
    private $client;
    private $driveService;
    private $accessToken;

    public function __construct($accessToken = null, $refreshToken = null)
    {
        $this->client = new \Google\Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $this->client->addScope(\Google\Service\Drive::DRIVE_FILE);
        $this->client->addScope(\Google\Service\Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        if ($accessToken) {
            $this->client->setAccessToken($accessToken);

            // Refresh token jika expired
            if ($this->client->isAccessTokenExpired() && $refreshToken) {
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                $this->accessToken = $this->client->getAccessToken();
            }

            $this->driveService = new \Google\Service\Drive($this->client);
        }
    }

    /**
     * Get fresh access token
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    /**
     * Check if token is expired
     */
    public function isTokenExpired()
    {
        return $this->client->isAccessTokenExpired();
    }

    /**
     * Membuat folder baru di Google Drive
     */
    public function createFolder($name, $parentId = null)
    {
        try {
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            if ($parentId) {
                $fileMetadata->setParents([$parentId]);
            }

            $folder = $this->driveService->files->create($fileMetadata, [
                'fields' => 'id, name, webViewLink'
            ]);

            return [
                'success' => true,
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'url' => $folder->getWebViewLink()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload file ke Google Drive
     */
    public function uploadFile($filePath, $fileName, $folderId = null, $mimeType = null)
    {
        try {
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $fileName
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $content = file_get_contents($filePath);

            if (!$mimeType) {
                $mimeType = mime_content_type($filePath);
            }

            $file = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink, webContentLink, size, mimeType'
            ]);

            // Set permission to anyone with link can view
            $permission = new \Google\Service\Drive\Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);
            $this->driveService->permissions->create($file->getId(), $permission);

            return [
                'success' => true,
                'id' => $file->getId(),
                'name' => $file->getName(),
                'url' => $file->getWebViewLink(),
                'downloadUrl' => $file->getWebContentLink(),
                'size' => $file->getSize(),
                'mimeType' => $file->getMimeType()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload dari $_FILES
     */
    public function uploadFromPost($fileInput, $folderId = null)
    {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'File upload error'
            ];
        }

        $file = $_FILES[$fileInput];
        return $this->uploadFile(
            $file['tmp_name'],
            $file['name'],
            $folderId,
            $file['type']
        );
    }

    /**
     * List files dalam folder
     */
    public function listFiles($folderId, $pageSize = 100)
    {
        try {
            $query = "'{$folderId}' in parents and trashed = false";

            $response = $this->driveService->files->listFiles([
                'q' => $query,
                'pageSize' => $pageSize,
                'fields' => 'files(id, name, mimeType, size, webViewLink, webContentLink, createdTime, modifiedTime)'
            ]);

            $files = [];
            foreach ($response->getFiles() as $file) {
                $files[] = [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'url' => $file->getWebViewLink(),
                    'downloadUrl' => $file->getWebContentLink(),
                    'createdTime' => $file->getCreatedTime(),
                    'modifiedTime' => $file->getModifiedTime(),
                    'isFolder' => $file->getMimeType() === 'application/vnd.google-apps.folder'
                ];
            }

            return [
                'success' => true,
                'files' => $files
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Hapus file
     */
    public function deleteFile($fileId)
    {
        try {
            $this->driveService->files->delete($fileId);
            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get file info
     */
    public function getFile($fileId)
    {
        try {
            $file = $this->driveService->files->get($fileId, [
                'fields' => 'id, name, mimeType, size, webViewLink, webContentLink, createdTime'
            ]);

            return [
                'success' => true,
                'file' => [
                    'id' => $file->getId(),
                    'name' => $file->getName(),
                    'mimeType' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'url' => $file->getWebViewLink(),
                    'downloadUrl' => $file->getWebContentLink(),
                    'createdTime' => $file->getCreatedTime()
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
     * Buat struktur folder untuk mata kuliah
     * Format: SIAPGRAK/Semester X/Kelas Y/MataKuliah/Pertemuan N
     */
    public function createMataKuliahStructure($rootFolderId, $semesterNama, $kelasNama, $mataKuliahNama)
    {
        $folders = [];

        // Buat folder semester
        $semesterFolder = $this->createFolder($semesterNama, $rootFolderId);
        if (!$semesterFolder['success']) return $semesterFolder;
        $folders['semester'] = $semesterFolder;

        // Buat folder kelas
        $kelasFolder = $this->createFolder($kelasNama, $semesterFolder['id']);
        if (!$kelasFolder['success']) return $kelasFolder;
        $folders['kelas'] = $kelasFolder;

        // Buat folder mata kuliah
        $mkFolder = $this->createFolder($mataKuliahNama, $kelasFolder['id']);
        if (!$mkFolder['success']) return $mkFolder;
        $folders['mata_kuliah'] = $mkFolder;

        // Buat folder pertemuan P1-P18
        $folders['pertemuan'] = [];
        for ($i = 1; $i <= 18; $i++) {
            $pFolder = $this->createFolder("Pertemuan $i", $mkFolder['id']);
            if (!$pFolder['success']) return $pFolder;
            $folders['pertemuan'][$i] = $pFolder;
        }

        return [
            'success' => true,
            'folders' => $folders
        ];
    }

    /**
     * Buat root folder SIAPGRAK untuk mahasiswa baru
     */
    public function createRootFolder($mahasiswaNama)
    {
        return $this->createFolder("SIAPGRAK - $mahasiswaNama");
    }
}
