<?php
$zipFile = 'absensi_smkalfarizi.zip';
if (file_exists($zipFile)) unlink($zipFile);

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Failed to create zip file");
}

$rootPath = realpath('.');
$excludes = ['.git', 'AndroidGuruApp', '.env', 'absensi_smkalfarizi.zip', 'absensi_smkalfarizi_deploy.zip', 'create_zip.php'];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        $skip = false;
        foreach ($excludes as $ex) {
            if (strpos($relativePath, $ex) === 0) {
                $skip = true;
                break;
            }
        }
        if (!$skip) {
            $zip->addFile($filePath, $relativePath);
        }
    }
}
$zip->close();
echo "Zip created successfully.\n";
