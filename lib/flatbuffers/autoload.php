<?php 

function nanoapi_autoload($class_name) {
    $class = substr($class_name, strrpos($class_name, "\\"));
    $root_dir = join(DIRECTORY_SEPARATOR, array(dirname(dirname(__FILE__)))); // `flatbuffers` root.
    $paths = array(join(DIRECTORY_SEPARATOR, array($root_dir, "flatbuffers/nanoapi")));
    foreach ($paths as $path) {
        $file = join(DIRECTORY_SEPARATOR, array($path, $class . ".php"));
        if (file_exists($file)) {
            require($file);
            break;
        }
    }
}

$files = scandir(__DIR__ . '/nanoapi');

foreach ($files as $file) {
    if ($file == '.' ||
        $file == '..'
    ) {
        continue;
    }
    list($name, $extension) = explode('.', $file);
    nanoapi_autoload($name);
}
