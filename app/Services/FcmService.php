<?php

namespace App\Services;

use Firebase\JWT\JWT;

class FcmService
{
    private $serviceAccount;
    private $projectId;

    public function __construct()
    {
        $configPath = __DIR__ . '/../Config/firebase-service-account.json';
        if (file_exists($configPath)) {
            $this->serviceAccount = json_decode(file_get_contents($configPath), true);
            $this->projectId = $this->serviceAccount['project_id'] ?? null;
        }
    }

    /**
     * Generate OAuth 2.0 Access Token manually via JWT Assertion
     * (Requires firebase/php-jwt package)
     */
    private function getAccessToken()
    {
        if (!$this->serviceAccount) {
            return null;
        }

        $now = time();
        $payload = [
            'iss' => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        // Sign the JWT with the private key
        $jwt = JWT::encode($payload, $this->serviceAccount['private_key'], 'RS256');

        // Exchange JWT for an access token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }

        return null;
    }

    /**
     * Kirim push notification via HTTP v1 API
     */
    public function sendToToken($fcmToken, $title, $body, $url = '/siswa', $dataPayload = [])
    {
        if (!$this->serviceAccount || !$this->projectId || empty($fcmToken)) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge([
                    'url' => $url,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ], $dataPayload),
                'webpush' => [
                    'notification' => [
                        'icon' => '/icons/icon-192.png',
                        'badge' => '/icons/icon-192.png',
                        'data' => [
                            'url' => $url
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Broadcast notification to multiple tokens (chunked)
     */
    public function sendToTokens(array $tokens, $title, $body, $url = '/siswa', $dataPayload = [])
    {
        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $url, $dataPayload)) {
                $successCount++;
            }
        }
        return $successCount;
    }
}
