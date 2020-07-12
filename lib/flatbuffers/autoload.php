<?php 

$files = scandir(__DIR__ . '/nanoapi');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        require __DIR__ . '/nanoapi/' . $file;
    }
}
