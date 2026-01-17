<?php

namespace App\Services;

use App\Helpers\JsonDatabase;
use App\Helpers\FonnteHelper;

class NotificationService
{
    private $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Check deadlines and send notifications
     */
    public function checkDeadlinesAndSend()
    {
        $this->log("Checking deadlines...");

        $allTugas = $this->db->all('tugas');
        $notifications = $this->db->all('tugas_notifications');

        $now = time();
        $updatesCount = 0;

        foreach ($allTugas as $tugas) {
            if ((!empty($tugas['is_completed']) && $tugas['is_completed']) || empty($tugas['deadline'])) {
                continue;
            }

            $pertemuan = $this->db->findById('pertemuan', $tugas['pertemuan_id']);
            if (!$pertemuan) continue;

            $mk = $this->db->findById('mata_kuliah', $pertemuan['mata_kuliah_id']);
            if (!$mk) continue;

            $mahasiswaId = $mk['mahasiswa_id'];
            $mahasiswa = $this->db->findById('mahasiswa', $mahasiswaId);

            if (!$mahasiswa || empty($mahasiswa['no_hp'])) {
                continue;
            }

            $deadline = strtotime($tugas['deadline']);
            $diffSeconds = $deadline - $now;

            // Log for debug
            $this->log("Task: {$tugas['judul']} - Diff: $diffSeconds");

            // Handle Overdue (Negative diff)
            if ($diffSeconds < 0) {
                // If overdue by less than 24 hours, send ONE "Terlewat" notification if not sent yet
                if ($diffSeconds > -86400 && !$this->isNotificationSent($tugas['id'], 'overdue', $notifications)) {
                    $this->sendNotification($mahasiswa, $tugas, $mk['nama_mk'], 'overdue', $diffSeconds);
                    $updatesCount++;
                }
                continue;
            }

            // Normal Deadlines (Sorted from Urgent to less urgent)
            $checks = [
                '1_hour' => 1 * 3600,
                '6_hours' => 6 * 3600,
                '12_hours' => 12 * 3600,
                '1_day' => 1 * 24 * 3600,
                '2_days' => 2 * 24 * 3600
            ];

            // Iterate from most urgent (smallest time) to least urgent
            foreach ($checks as $type => $threshold) {
                if ($diffSeconds <= $threshold) {
                    // This threshold is met. Check if we already sent THIS specific alert.
                    if (!$this->isNotificationSent($tugas['id'], $type, $notifications)) {
                        $this->log("Sending $type for {$tugas['judul']}");
                        $this->sendNotification($mahasiswa, $tugas, $mk['nama_mk'], $type, $diffSeconds);
                        $updatesCount++;
                        // Break after sending the most urgent one to avoid spamming "1 day" AND "2 days" at same time
                        break;
                    } else {
                        // If we already sent the '1 hour' alert, we don't need to send '2 days'.
                        // We also shouldn't check further down? 
                        // If '1 hour' matches, we don't want to check '6 hours'.
                        break;
                    }
                }
            }
        }

        $this->log("Check complete. Sent $updatesCount notifications.");
        return $updatesCount;
    }

    private function isNotificationSent($tugasId, $type, $notifications)
    {
        foreach ($notifications as $notif) {
            if ($notif['tugas_id'] == $tugasId && $notif['type'] == $type) {
                return true;
            }
        }
        return false;
    }

    private function sendNotification($user, $tugas, $mkName, $type, $diffSeconds)
    {
        $phone = $this->sanitizePhone($user['no_hp']);

        $timeLeft = $this->formatTimeLeft($diffSeconds);
        if ($type === 'overdue') {
            $header = "*⚠️ TUGAS TERLEWAT!*";
            $body = "Tugas ini sudah melewati deadline ($timeLeft lalu). Segera kumpulkan jika masih memungkinkan!";
        } else {
            $header = "*⏰ PENGINGAT DEADLINE!*";
            $body = "Tugas akan berakhir dalam *$timeLeft*.";
        }

        $message = "$header\n\n";
        $message .= "Halo {$user['nama']},\n";
        $message .= "$body\n\n";
        $message .= "📚 Matkul: $mkName\n";
        $message .= "📝 Tugas: {$tugas['judul']}\n";
        $message .= "📅 Deadline: " . date('d M Y, H:i', strtotime($tugas['deadline'])) . "\n\n";
        $message .= "Semangat! 🚀\n";
        $message .= "_Sistem Akademik_";

        $res = FonnteHelper::send($phone, $message);

        $this->log("Fonnte Response for {$tugas['id']} ($type): " . json_encode($res));

        $this->db->insert('tugas_notifications', [
            'tugas_id' => $tugas['id'],
            'type' => $type,
            'sent_at' => date('Y-m-d H:i:s'),
            'status' => $res['status'] ?? false,
            'response' => json_encode($res)
        ]);
    }

    private function formatTimeLeft($seconds)
    {
        $seconds = abs($seconds);
        if ($seconds < 3600) return floor($seconds / 60) . " Menit";
        if ($seconds < 86400) return floor($seconds / 3600) . " Jam";
        return floor($seconds / 86400) . " Hari";
    }

    private function sanitizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return $phone;
    }

    private function log($msg)
    {
        // Ensure path exists or simply write to public relative path
        $logFile = defined('PUBLIC_PATH') ? PUBLIC_PATH . '/notif_log.txt' : __DIR__ . '/../../public/notif_log.txt';
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
    }
}
