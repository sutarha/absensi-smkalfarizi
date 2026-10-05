<?php
$files = [
    __DIR__ . '/app/Views/admin/jadwal_index.php',
    __DIR__ . '/app/Views/admin/monitoring_guru.php',
    __DIR__ . '/app/Views/admin/tabungan_program.php',
    __DIR__ . '/app/Views/guru/tabungan_detail.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // The bad string that was inserted previously right after `?>`
    $badString = "\n                                                <?= \\App\\Helpers\\CsrfHelper::getTokenInput() ?>";
    
    if (strpos($content, $badString) !== false) {
        $content = str_replace($badString, "", $content);
        file_put_contents($file, $content);
        echo "Removed bad CSRF from " . basename($file) . "\n";
    }
}
