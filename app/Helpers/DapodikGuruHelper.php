<?php
namespace App\Helpers;

use App\Config\Database;
use PDO;

/**
 * Helper untuk Import dan Parsing Data Guru & PTK/Tendik dari Dapodik
 * Mendukung format berkas resmi ekspor Dapodik (.xlsx, .xls, .csv) maupun template mandiri.
 */
class DapodikGuruHelper
{
    /**
     * Parse Berkas Excel / CSV Guru dari Dapodik
     */
    public static function parseFile(string $filePath, ?string $originalName = null): array
    {
        $rawRows = ExcelHelper::parse($filePath, $originalName);
        if (empty($rawRows) || count($rawRows) < 2) {
            throw new \Exception("Berkas Excel harus memiliki minimal baris header dan data guru.");
        }

        // 1. Cari letak baris header secara dinamis (memindai hingga 15 baris pertama)
        $headerRowIdx = null;
        $scanLimit = min(15, count($rawRows));
        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $rawRows[$i] ?? [];
            $joined = strtolower(implode(' ', array_map('strval', $row)));
            if ((strpos($joined, 'nuptk') !== false || strpos($joined, 'nip') !== false) && 
                (strpos($joined, 'nama') !== false || strpos($joined, 'ptk') !== false || strpos($joined, 'guru') !== false)) {
                $headerRowIdx = $i;
                break;
            }
        }

        if ($headerRowIdx === null) {
            // Fallback baris 0
            $headerRowIdx = 0;
        }

        $headers = $rawRows[$headerRowIdx] ?? [];
        $map = self::mapHeaders($headers);

        if (!isset($map['nama'])) {
            throw new \Exception("Format berkas tidak valid! Kolom 'Nama' guru/PTK wajib ditemukan.");
        }

        $teachers = [];
        $existingUsernames = self::getExistingUsernames();

        for ($r = $headerRowIdx + 1; $r < count($rawRows); $r++) {
            $row = $rawRows[$r] ?? [];
            if (empty($row)) continue;

            $nama = trim((string)($row[$map['nama']] ?? ''));
            if (empty($nama)) continue;

            $nuptk = isset($map['nuptk']) ? preg_replace('/[^0-9]/', '', (string)($row[$map['nuptk']] ?? '')) : '';
            $nip = isset($map['nip']) ? preg_replace('/[^0-9]/', '', (string)($row[$map['nip']] ?? '')) : '';
            $nik = isset($map['nik']) ? preg_replace('/[^0-9]/', '', (string)($row[$map['nik']] ?? '')) : '';
            
            // Prioritas NIK/NIP: NIP > NUPTK > NIK
            $nikNip = !empty($nip) ? $nip : (!empty($nuptk) ? $nuptk : (!empty($nik) ? $nik : 'GURU-' . str_pad((string)$r, 4, '0', STR_PAD_LEFT)));

            $tugasTambahan = isset($map['tugas_tambahan']) ? trim((string)($row[$map['tugas_tambahan']] ?? '')) : '';
            
            // HP diprioritaskan dari kolom HP, jika kosong gunakan Telepon
            $hp = isset($map['hp']) ? trim((string)($row[$map['hp']] ?? '')) : '';
            $telepon = isset($map['telepon']) ? trim((string)($row[$map['telepon']] ?? '')) : '';
            $noHp = !empty($hp) ? $hp : $telepon;
            $noHp = preg_replace('/[^0-9\+]/', '', $noHp);

            $email = isset($map['email']) ? strtolower(trim((string)($row[$map['email']] ?? ''))) : '';
            $jk = isset($map['jk']) ? strtoupper(trim((string)($row[$map['jk']] ?? 'L'))) : 'L';
            $jk = (str_starts_with($jk, 'P') || str_starts_with($jk, 'W')) ? 'P' : 'L';

            // Role default guru, jika ada kata piket bisa jadi piket
            $role = 'guru';
            if (stripos($tugasTambahan, 'piket') !== false) {
                $role = 'piket';
            }

            // Generate Username unik yang ramah
            $suggestedUsername = self::generateUsername($nama, $email, $nikNip, $existingUsernames);
            $existingUsernames[$suggestedUsername] = true;

            $teachers[] = [
                'nama_lengkap' => $nama,
                'nik_nip' => $nikNip,
                'nuptk' => $nuptk,
                'nip' => $nip,
                'nik' => $nik,
                'username' => $suggestedUsername,
                'role' => $role,
                'tugas_tambahan' => $tugasTambahan !== '' ? $tugasTambahan : null,
                'no_hp' => $noHp !== '' ? $noHp : null,
                'email' => $email !== '' ? $email : null,
                'jenis_kelamin' => $jk
            ];
        }

        return $teachers;
    }

    /**
     * Memetakan kolom header Dapodik
     */
    private static function mapHeaders(array $headers): array
    {
        $map = [];
        foreach ($headers as $colIdx => $h) {
            $cleaned = strtolower(trim((string)$h));
            $cleaned = preg_replace('/[^a-z0-9]+/i', '_', $cleaned);
            $cleaned = trim($cleaned, '_');

            if (in_array($cleaned, ['nama', 'nama_lengkap', 'nama_ptk', 'nama_guru', 'nama_pegawai'], true) && !isset($map['nama'])) {
                $map['nama'] = $colIdx;
            } elseif (in_array($cleaned, ['nuptk', 'no_nuptk'], true) && !isset($map['nuptk'])) {
                $map['nuptk'] = $colIdx;
            } elseif (in_array($cleaned, ['nip', 'no_nip'], true) && !isset($map['nip'])) {
                $map['nip'] = $colIdx;
            } elseif (in_array($cleaned, ['nik', 'no_nik', 'nik_ptk'], true) && !isset($map['nik'])) {
                $map['nik'] = $colIdx;
            } elseif (in_array($cleaned, ['jk', 'jenis_kelamin'], true) && !isset($map['jk'])) {
                $map['jk'] = $colIdx;
            } elseif (in_array($cleaned, ['tugas_tambahan'], true) && !isset($map['tugas_tambahan'])) {
                $map['tugas_tambahan'] = $colIdx;
            } elseif (in_array($cleaned, ['hp', 'no_hp', 'no_handphone'], true) && !isset($map['hp'])) {
                $map['hp'] = $colIdx;
            } elseif (in_array($cleaned, ['telepon', 'no_telepon'], true) && !isset($map['telepon'])) {
                $map['telepon'] = $colIdx;
            } elseif (in_array($cleaned, ['email', 'e_mail'], true) && !isset($map['email'])) {
                $map['email'] = $colIdx;
            }
        }
        return $map;
    }

    /**
     * Menghasilkan Username Bersih dan Unik
     */
    private static function generateUsername(string $name, string $email, string $nikNip, array $existing): string
    {
        // 1. Coba dari bagian nama pertama (misal: "Alpi Surya" -> "alpi", "Arif Rahman" -> "arif")
        $words = preg_split('/\s+/', trim($name));
        $firstWord = strtolower(preg_replace('/[^a-z0-9]/i', '', $words[0] ?? 'guru'));
        
        $base = $firstWord;
        if (strlen($base) < 3 && isset($words[1])) {
            $base .= '.' . strtolower(preg_replace('/[^a-z0-9]/i', '', $words[1]));
        }

        if (!isset($existing[$base])) {
            return $base;
        }

        // 2. Jika nama pertama bentrok, coba kombinasi nama depan dan belakang
        if (count($words) > 1) {
            $lastWord = strtolower(preg_replace('/[^a-z0-9]/i', '', end($words)));
            $combo = $firstWord . '.' . $lastWord;
            if (!isset($existing[$combo])) {
                return $combo;
            }
        }

        // 3. Coba dari prefix email jika ada
        if (!empty($email) && strpos($email, '@') !== false) {
            $emailPrefix = strtolower(preg_replace('/[^a-z0-9_.]/i', '', explode('@', $email)[0]));
            if (!empty($emailPrefix) && !isset($existing[$emailPrefix])) {
                return $emailPrefix;
            }
        }

        // 4. Jika masih bentrok, tambahkan digit
        $suffix = 2;
        while (isset($existing[$base . $suffix])) {
            $suffix++;
        }
        return $base . $suffix;
    }

    /**
     * Ambil daftar username guru yang sudah ada di database
     */
    private static function getExistingUsernames(): array
    {
        $db = Database::getConnection();
        $rows = $db->query("SELECT username FROM guru")->fetchAll(PDO::FETCH_COLUMN);
        $map = [];
        foreach ($rows as $u) {
            $map[strtolower(trim($u))] = true;
        }
        return $map;
    }

    /**
     * Hasilkan Template Excel Guru Dapodik (.xlsx)
     */
    public static function getExcelTemplateContent(): string
    {
        $headers = [
            'No',
            'Nama Lengkap',
            'NUPTK',
            'JK',
            'NIP',
            'Status Kepegawaian',
            'Jenis PTK',
            'Tugas Tambahan',
            'HP',
            'Email',
            'NIK'
        ];

        $sampleRows = [
            [
                '1',
                'Alpi Surya Perdana, S.Kom.',
                '9941772673130332',
                'L',
                '',
                'GTY/PTY',
                'Guru',
                'Guru wali',
                '081223319152',
                'alpiperdana1@gmail.com',
                '3210010906940061'
            ],
            [
                '2',
                'Epa Parlina, S.Pd.',
                '6251764666300033',
                'P',
                '',
                'GTY/PTY',
                'Guru',
                'Wakil Kepala Sekolah Kurikulum',
                '085315784660',
                'epaparlina1986@gmail.com',
                '3210025909860041'
            ],
            [
                '3',
                'Duding Supriadi, S.T.',
                '8738767668130202',
                'L',
                '',
                'GTY/PTY',
                'Guru',
                'Guru wali',
                '085210000946',
                'dudingsupriadi@gmail.com',
                '3210020404890001'
            ]
        ];

        return ExcelHelper::createXlsx('Data Guru Dapodik', $headers, $sampleRows);
    }
}
