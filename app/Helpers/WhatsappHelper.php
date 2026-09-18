<?php
namespace App\Helpers;

use App\Config\App;

class WhatsappHelper
{
    /**
     * Kirim pesan WhatsApp menggunakan Fonnte API
     * 
     * @param string $target Nomor tujuan (contoh: '08123456789')
     * @param string $message Isi pesan
     * @return array [success => bool, message => string, response => mixed]
     */
    public static function sendMessage(string $target, string $message): array
    {
        $env = parse_ini_file(__DIR__ . '/../../.env');
        $token = $env['FONNTE_TOKEN'] ?? '';
        
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token Fonnte belum dikonfigurasi di .env',
                'response' => null
            ];
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
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: {$token}"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return [
                'success' => false,
                'message' => "cURL Error #:" . $err,
                'response' => null
            ];
        } else {
            $decoded = json_decode($response, true);
            return [
                'success' => isset($decoded['status']) && $decoded['status'] == true,
                'message' => $decoded['reason'] ?? 'Berhasil dikirim',
                'response' => $decoded
            ];
        }
    }
}
