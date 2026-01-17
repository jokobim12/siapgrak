<?php

namespace App\Helpers;

class FonnteHelper
{
    private static $token;

    public static function init()
    {
        if (defined('FONNTE_TOKEN')) {
            self::$token = FONNTE_TOKEN;
        } else {
            // Fallback or handle missing token
            self::$token = '';
        }
    }

    /**
     * Send WhatsApp message via Fonnte
     *
     * @param string $target Phone number
     * @param string $message Message content
     * @return array Response from Fonnte
     */
    public static function send($target, $message)
    {
        if (empty(self::$token)) {
            self::init();
        }

        if (empty(self::$token)) {
            return ['status' => false, 'reason' => 'Token not configured'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default to Indonesia
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . self::$token
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return ['status' => false, 'reason' => $error];
        }

        return json_decode($response, true);
    }
}
