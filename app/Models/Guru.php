<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Guru
{
    public static function findByUsername(string $username): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guru WHERE username = ? OR nik_nip = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM guru WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getAll($role = null): array
    {
        $db = Database::getConnection();
        if (is_array($role)) {
            $inQuery = implode(',', array_fill(0, count($role), '?'));
            $stmt = $db->prepare("SELECT * FROM guru WHERE role IN ($inQuery) AND username != 'admin' ORDER BY nama_lengkap ASC");
            $stmt->execute($role);
        } elseif ($role) {
            $stmt = $db->prepare("SELECT * FROM guru WHERE role = :role AND username != 'admin' ORDER BY nama_lengkap ASC");
            $stmt->execute([':role' => $role]);
        } else {
            $stmt = $db->query("SELECT * FROM guru WHERE username != 'admin' ORDER BY nama_lengkap ASC");
        }
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $sql = "INSERT INTO guru (nik_nip, nama_lengkap, username, password, role, tugas_tambahan, tunjangan_tugas, no_hp)
                VALUES (:nik_nip, :nama_lengkap, :username, :password, :role, :tugas_tambahan, :tunjangan_tugas, :no_hp)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nik_nip' => $data['nik_nip'],
            ':nama_lengkap' => $data['nama_lengkap'],
            ':username' => $data['username'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':role' => $data['role'] ?? 'guru',
            ':tugas_tambahan' => $data['tugas_tambahan'] ?? null,
            ':tunjangan_tugas' => (float)($data['tunjangan_tugas'] ?? 0.0),
            ':no_hp' => $data['no_hp'] ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $fields = [
            'nik_nip = :nik_nip',
            'nama_lengkap = :nama_lengkap',
            'username = :username',
            'role = :role',
            'tugas_tambahan = :tugas_tambahan',
            'tunjangan_tugas = :tunjangan_tugas',
            'no_hp = :no_hp',
        ];

        $params = [
            ':id' => $id,
            ':nik_nip' => $data['nik_nip'],
            ':nama_lengkap' => $data['nama_lengkap'],
            ':username' => $data['username'],
            ':role' => $data['role'],
            ':tugas_tambahan' => $data['tugas_tambahan'] ?? null,
            ':tunjangan_tugas' => (float)($data['tunjangan_tugas'] ?? 0.0),
            ':no_hp' => $data['no_hp'] ?? null,
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql = "UPDATE guru SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM guru WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Bulk Import / Upsert Data Guru dari Hasil Ekspor Dapodik
     */
    public static function bulkUpsertDapodik(array $teachers, string $defaultPassword = 'guru123'): array
    {
        $db = Database::getConnection();
        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        $stmtFindByNik = $db->prepare("SELECT * FROM guru WHERE nik_nip = ? LIMIT 1");
        $stmtFindByName = $db->prepare("SELECT * FROM guru WHERE LOWER(TRIM(nama_lengkap)) = ? LIMIT 1");
        $stmtFindByUser = $db->prepare("SELECT * FROM guru WHERE username = ? LIMIT 1");

        $stmtInsert = $db->prepare("
            INSERT INTO guru (nik_nip, nama_lengkap, username, password, role, tugas_tambahan, tunjangan_tugas, no_hp)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmtUpdate = $db->prepare("
            UPDATE guru SET 
                nama_lengkap = ?,
                nik_nip = ?,
                tugas_tambahan = COALESCE(?, tugas_tambahan),
                no_hp = COALESCE(?, no_hp)
            WHERE id = ?
        ");

        $defaultHashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);

        foreach ($teachers as $idx => $t) {
            $line = $idx + 1;
            $nama = trim((string)($t['nama_lengkap'] ?? ''));
            $nikNip = trim((string)($t['nik_nip'] ?? ''));
            $username = trim((string)($t['username'] ?? ''));
            $role = $t['role'] ?? 'guru';
            $tugas = !empty($t['tugas_tambahan']) ? trim((string)$t['tugas_tambahan']) : null;
            $noHp = !empty($t['no_hp']) ? trim((string)$t['no_hp']) : null;

            if (empty($nama)) {
                $stats['failed']++;
                $stats['errors'][] = "Baris #{$line}: Nama guru kosong.";
                continue;
            }

            try {
                // Cari guru yang sudah ada (berdasarkan nik_nip, atau nama lengkap, atau username)
                $existing = null;
                if (!empty($nikNip)) {
                    $stmtFindByNik->execute([$nikNip]);
                    $existing = $stmtFindByNik->fetch();
                }
                if (!$existing) {
                    $stmtFindByName->execute([strtolower($nama)]);
                    $existing = $stmtFindByName->fetch();
                }
                if (!$existing && !empty($username)) {
                    $stmtFindByUser->execute([$username]);
                    $existing = $stmtFindByUser->fetch();
                }

                if ($existing) {
                    // Update data profil guru tanpa mereset password, username, role, atau tunjangan_tugas
                    $stmtUpdate->execute([
                        $nama,
                        !empty($nikNip) ? $nikNip : $existing['nik_nip'],
                        $tugas,
                        $noHp,
                        (int)$existing['id']
                    ]);
                    $stats['updated']++;
                } else {
                    // Insert guru baru
                    $stmtInsert->execute([
                        $nikNip,
                        $nama,
                        $username,
                        $defaultHashedPassword,
                        $role,
                        $tugas,
                        0.00,
                        $noHp
                    ]);
                    $stats['inserted']++;
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                $stats['errors'][] = "Baris #{$line} ({$nama}): " . $e->getMessage();
            }
        }

        return $stats;
    }
}
