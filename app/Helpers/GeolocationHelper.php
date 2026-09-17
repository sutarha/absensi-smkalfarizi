<?php
namespace App\Helpers;

class GeolocationHelper
{
    public const EARTH_RADIUS_METERS = 6371000;

    /**
     * Menghitung jarak antara dua koordinat GPS dalam satuan meter menggunakan rumus Haversine
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Memeriksa apakah koordinat pengguna berada dalam radius sekolah
     */
    public static function isWithinRadius(
        float $userLat,
        float $userLon,
        float $schoolLat,
        float $schoolLon,
        int $radiusMeters
    ): array {
        $distance = self::calculateDistance($userLat, $userLon, $schoolLat, $schoolLon);
        $isInside = $distance <= $radiusMeters;

        return [
            'is_valid' => $isInside,
            'distance_meters' => $distance,
            'radius_limit' => $radiusMeters,
            'difference' => $isInside ? 0 : round($distance - $radiusMeters, 2)
        ];
    }
}
