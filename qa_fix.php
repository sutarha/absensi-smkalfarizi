<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/app/Views');
$ite = new RecursiveIteratorIterator($dir);

foreach ($ite as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        $modified = false;
        
        // Find action attribute that contains getTokenInput() and fix it
        // Pattern: action="URL <?= \App\Helpers\CsrfHelper::getTokenInput() ?> "
        // Replace with: action="URL" > <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
        
        // It's safer to use preg_replace_callback
        $newContent = preg_replace_callback('/(action\s*=\s*(["\']))((?:(?!\2).)*?)<\?=\s*\\\\App\\\\Helpers\\\\CsrfHelper::getTokenInput\(\)\s*\?>\s*\2/is', function($matches) {
            $attrStart = $matches[1]; // action=" or action='
            $quote = $matches[2]; // " or '
            $urlPart = trim($matches[3]);
            
            // Reconstruct: action="URL"> \n <?= ... ?> 
            // Wait, this will replace the closing quote of the action attribute, but what about the > of the form?
            // Actually, the original bug was: 
            // <form action="URL <?= getTokenInput() ?>">
            // So we just want to remove the getTokenInput from inside the quotes, and put it right after the closing > of the form tag.
            // But we don't match the closing > here.
            return $matches[0]; // skip for now
        }, $content);
        
        // Let's do a simpler regex like in fix_bug.php
        $pattern = '/(action\s*=\s*["\'][^"\']*?)\s*<\?=\s*\\\\App\\\\Helpers\\\\CsrfHelper::getTokenInput\(\)\s*\?>\s*(["\'])/is';
        
        if (preg_match($pattern, $content)) {
            // we remove it from inside the quotes, and inject it after the next closing >
            // This requires careful parsing. Let's do it manually on the file level
            $newContent2 = preg_replace_callback('/(<form[^>]*?)(action\s*=\s*["\'][^"\']*?)\s*<\?=\s*\\\\App\\\\Helpers\\\\CsrfHelper::getTokenInput\(\)\s*\?>\s*(["\'])([^>]*>)/is', function($matches) {
                $formStart = $matches[1];
                $actionStart = $matches[2]; // action="URL
                $actionEnd = $matches[3]; // "
                $formEnd = $matches[4]; // rest of form tag >
                
                return $formStart . trim($actionStart) . $actionEnd . $formEnd . "\n    <?= \\App\\Helpers\\CsrfHelper::getTokenInput() ?>";
            }, $content);
            
            if ($newContent2 !== $content) {
                file_put_contents($path, $newContent2);
                echo "Fixed: " . basename($path) . "\n";
            } else {
                echo "Failed to fix with regex: " . basename($path) . "\n";
            }
        }
    }
}
