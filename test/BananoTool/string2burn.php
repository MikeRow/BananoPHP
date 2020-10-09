<?php

require __DIR__ . '/../autoload.php';

use MikeRow\BananoPHP\BananoTool;

$burn = BananoTool::string2burn('he11o', '1', '1');

var_dump($burn);
