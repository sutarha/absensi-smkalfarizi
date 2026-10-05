<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/app');
$ite = new RecursiveIteratorIterator($dir);
$issues = [];

foreach ($ite as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        // 1. Check for CSRF inside action attribute (the bug we've been fixing)
        if (preg_match_all('/action\s*=\s*(["\'])(?:(?!\1).)*?getTokenInput.*?\1/is', $content, $matches)) {
             $issues[$path][] = "Found CSRF token inside action attribute!";
        }
        
        // 2. Check for CSRF inside ANY attribute (just to be safe)
        // Check omitted
        
        // 3. Check for PHP syntax errors (Lint)
        exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return_var);
        if ($return_var !== 0) {
             $issues[$path][] = "Syntax Error: " . implode(" ", $output);
        }
    }
}

if (empty($issues)) {
    echo "QA Scan Complete: No major static issues found.\n";
} else {
    echo "QA Scan Found Issues:\n";
    foreach ($issues as $file => $fileIssues) {
        echo "File: $file\n";
        foreach ($fileIssues as $issue) {
            echo "  - $issue\n";
        }
    }
}
