<?php 

spl_autoload_register(
    function($class) {
        $class = substr($class, strrpos($class, "\\") + 1);
        $root_dir = join(DIRECTORY_SEPARATOR, array(dirname(dirname(__FILE__)))); // `flatbuffers` root.
        $paths = array(join(DIRECTORY_SEPARATOR, array($root_dir, "flatbuffers/nanoapi")));
        foreach ($paths as $path) {
            $file = join(DIRECTORY_SEPARATOR, array($path, $class . ".php"));
            if (file_exists($file)) {
                require $file;
                break;
            }
        }
    }
);
