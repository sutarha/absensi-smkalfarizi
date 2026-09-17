<?php
namespace App\Helpers;

use DateTime;

class TimeHelper
{
    public const DAYS_ID = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];

    public const MONTHS_ID = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public static function getDayName(?string $date = null): string
    {
        $d = $date ? new DateTime($date) : new DateTime();
        $englishDay = $d->format('l');
        return self::DAYS_ID[$englishDay] ?? $englishDay;
    }

    public static function formatDateIndonesian(string $date): string
    {
        $d = new DateTime($date);
        $day = self::getDayName($date);
        $m = (int)$d->format('n');
        $monthName = self::MONTHS_ID[$m] ?? '';
        return "{$day}, " . $d->format('d') . " {$monthName} " . $d->format('Y');
    }

    public static function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Memeriksa apakah waktu sekarang sudah masuk ke dalam jendela aktivasi tombol (H-5 menit sebelum jadwal mulai)
     * dan belum melewati jam selesai mengajar (jika lewat, presensi ditutup dan dianggap tidak hadir).
     * 
     * @param string $startTime Waktu mulai jadwal format 'H:i:s' atau 'H:i'
     * @param int $windowMinutes Menit toleransi sebelum mulai (default 5 menit)
     * @param string|null $nowTime Waktu pembanding (default waktu sekarang)
     * @param string|null $endTime Waktu selesai jadwal format 'H:i:s' atau 'H:i'
     * @return array [can_checkin => bool, status => string, seconds_remaining => int, message => string, is_late => bool, is_closed => bool]
     */
    public static function checkActivationWindow(string $startTime, int $windowMinutes = 5, ?string $nowTime = null, ?string $endTime = null): array
    {
        $today = date('Y-m-d');
        $now = $nowTime ? new DateTime("{$today} {$nowTime}") : new DateTime();
        $start = new DateTime("{$today} {$startTime}");

        // Waktu buka = Start - windowMinutes
        $openTime = clone $start;
        $openTime->modify("-{$windowMinutes} minutes");

        $nowTimestamp = $now->getTimestamp();
        $openTimestamp = $openTime->getTimestamp();
        $startTimestamp = $start->getTimestamp();

        // 1. Cek jika sudah melewati jam selesai mengajar (Kelewat jam ngajar -> Ditutup / Tidak Hadir)
        if ($endTime !== null) {
            $end = new DateTime("{$today} {$endTime}");
            if ($nowTimestamp >= $end->getTimestamp()) {
                return [
                    'can_checkin' => false,
                    'status' => 'CLOSED',
                    'seconds_remaining' => 0,
                    'minutes_remaining' => 0,
                    'is_late' => true,
                    'is_closed' => true,
                    'message' => "Jam mengajar telah berakhir pada pukul " . substr($endTime, 0, 5) . " WIB. Presensi KBM ditutup dan Anda dianggap Tidak Hadir."
                ];
            }
        }

        // 2. Cek jika belum masuk jendela H-5 (Terkunci)
        if ($nowTimestamp < $openTimestamp) {
            $diffSeconds = $openTimestamp - $nowTimestamp;
            $diffMinutes = ceil($diffSeconds / 60);
            return [
                'can_checkin' => false,
                'status' => 'LOCKED',
                'seconds_remaining' => $diffSeconds,
                'minutes_remaining' => $diffMinutes,
                'is_late' => false,
                'is_closed' => false,
                'message' => "Tombol presensi akan terbuka tepat {$windowMinutes} menit sebelum jam mengajar ({$openTime->format('H:i')}). Sisa waktu: {$diffMinutes} menit."
            ];
        }

        // 3. Masuk rentang jam mengajar (Siap check-in)
        return [
            'can_checkin' => true,
            'status' => 'OPEN',
            'seconds_remaining' => 0,
            'minutes_remaining' => 0,
            'is_late' => $nowTimestamp > $startTimestamp,
            'is_closed' => false,
            'message' => 'Tombol presensi aktif! Silakan lakukan check-in KBM.'
        ];
    }

    /**
     * Menghitung keterlambatan dan kalkulasi honor sesuai PRD:
     * - Tarif per Menit = Rp 5.000 / 40 = Rp 125/menit
     * - Plafon = N JP * Rp 5.000
     * - Durasi Total = N JP * 40 menit
     * - Jika T_aktual <= T_mulai -> Menit Telat = 0, Denda = 0, Honor 100%
     * - Jika T_aktual > T_mulai -> Menit Telat = round((T_aktual - T_mulai) / 60s)
     * - Durasi Efektif = max(0, Durasi Total - Menit Telat)
     * - Denda = Menit Telat * Rp 125
     * - Honor Diperoleh = Durasi Efektif * Rp 125
     */
    public static function calculateHonor(
        int $jumlahJp,
        string $jamMulai,
        string $waktuCheckin,
        float $honorPerJp = 5000.0,
        int $durasiJpMenit = 40,
        float $dendaPerMenit = 125.0
    ): array {
        $checkinDt = new DateTime($waktuCheckin);
        $dateStr = $checkinDt->format('Y-m-d');
        $startDt = new DateTime("{$dateStr} {$jamMulai}");

        $durasiTotalMenit = $jumlahJp * $durasiJpMenit;
        $plafonHonor = $jumlahJp * $honorPerJp;

        $checkinSec = $checkinDt->getTimestamp();
        $startSec = $startDt->getTimestamp();

        if ($checkinSec <= $startSec) {
            $menitTerlambat = 0;
            $durasiEfektif = $durasiTotalMenit;
            $potonganDenda = 0.0;
            $honorDidapat = $plafonHonor;
            $statusTelat = 'Tepat Waktu';
        } else {
            $menitTerlambat = (int)round(($checkinSec - $startSec) / 60);
            $durasiEfektif = max(0, $durasiTotalMenit - $menitTerlambat);
            $potonganDenda = $menitTerlambat * $dendaPerMenit;
            $honorDidapat = $durasiEfektif * $dendaPerMenit;
            $statusTelat = "Terlambat {$menitTerlambat} Menit";
        }

        return [
            'jumlah_jp' => $jumlahJp,
            'durasi_total_menit' => $durasiTotalMenit,
            'plafon_honor' => $plafonHonor,
            'menit_terlambat' => $menitTerlambat,
            'durasi_efektif_menit' => $durasiEfektif,
            'potongan_denda' => $potonganDenda,
            'honor_didapat' => $honorDidapat,
            'status_telat' => $statusTelat,
            'tarif_per_menit' => $dendaPerMenit
        ];
    }
}
