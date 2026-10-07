<?php

namespace App\Services;

class OneSignalService
{
    private $appId;
    private $restApiKey;

    public function __construct()
    {
        // Sebaiknya ini diletakkan di .env atau konfigurasi database
        // APP ID yang digunakan oleh Android Guru App
        $appId = getenv('ONESIGNAL_APP_ID') ?: '70c61e2a-caba-49bb-a9d4-97776600a225';
        $apiKey = getenv('ONESIGNAL_REST_API_KEY') ?: 'YOUR_REST_API_KEY';
        
        $this->appId = trim($appId, ' "\'');
        $this->restApiKey = trim($apiKey, ' "\'');
    }

    /**
     * Broadcast pengumuman via OneSignal
     * 
     * @param string $title Judul notifikasi
     * @param string $message Isi pesan notifikasi
     * @param string $url URL yang akan dibuka jika notifikasi di-klik (opsional)
     * @param string|null $targetRole Target role (misal: 'siswa', 'guru', atau null untuk semua)
     * @return bool
     */
    public function sendBroadcast(string $title, string $message, string $url = null, ?string $targetRole = null)
    {
        if (empty($this->appId) || empty($this->restApiKey) || $this->restApiKey === 'YOUR_REST_API_KEY') {
            error_log("OneSignalService: REST API Key belum di-setting.");
            return false; // Jangan lempar exception agar tidak memutus alur program
        }

        $fields = [
            'app_id' => $this->appId,
            'headings' => ["en" => $title],
            'contents' => ["en" => $message],
        ];

        if ($targetRole) {
            // Gunakan filter berdasarkan tag "role" yang sudah didaftarkan di frontend
            $fields['filters'] = [
                ["field" => "tag", "key" => "role", "relation" => "=", "value" => $targetRole]
            ];
        } else {
            $fields['included_segments'] = ['Total Subscriptions'];
        }

        if ($url) {
            // URL PWA
            $fields['url'] = $url;
            // Agar bisa dibuka di webview/browser external Android
            $fields['app_url'] = $url;
        }

        $fieldsJson = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.onesignal.com/notifications?c=push");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $this->restApiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsJson);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("OneSignalService Error: HTTP $httpCode - Response: $response - cURL Error: $error");
            return false;
        }
    }
}
