<?php
// Script to generate dummy PWA icons

$dir = __DIR__ . '/public/icons';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

function createIcon($size, $filename) {
    $image = imagecreatetruecolor($size, $size);
    // Background color: #0284c7 (Brand color)
    $bg = imagecolorallocate($image, 2, 132, 199);
    imagefill($image, 0, 0, $bg);

    // Text color: white
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Add some text
    $fontSize = $size / 4;
    $fontPath = 'C:\Windows\Fonts\arialbd.ttf'; // Use Arial Bold
    $text = "SMK";
    
    if (file_exists($fontPath)) {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $x = ($size - ($bbox[2] - $bbox[0])) / 2;
        $y = ($size - ($bbox[5] - $bbox[3])) / 2;
        imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);
    } else {
        // Fallback
        imagestring($image, 5, $size/3, $size/2 - 10, $text, $textColor);
    }

    imagepng($image, $filename);
    imagedestroy($image);
    echo "Created: $filename\n";
}

createIcon(192, $dir . '/icon-192.png');
createIcon(512, $dir . '/icon-512.png');
