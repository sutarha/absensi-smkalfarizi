<?php
require_once __DIR__ . '/../app/Config/Database.php';
try {
    $db = \App\Config\Database::getConnection();
    $db->exec('ALTER TABLE lms_materi ADD COLUMN konten LONGTEXT NULL AFTER deskripsi;');
    $db->exec('ALTER TABLE lms_submission CHANGE jawaban_teks jawaban TEXT NULL;');
    echo "Fixed.";
} catch (Exception $e) {
    echo $e->getMessage();
}
