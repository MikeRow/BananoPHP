<?php

if (file_exists(__DIR__ . '/../../../autoload.php')) {
    require __DIR__ . '/../../../autoload.php';
} else {
    require __DIR__ . '/../vendor/autoload.php';
    
    $path = __DIR__ . '/../src';
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file == '.' ||
            $file == '..'
        ) {
            continue;
        }
        require_once $path . '/' . $file;
    }
}
