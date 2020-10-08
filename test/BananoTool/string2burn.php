<?php

require __DIR__ . '/../autoload.php';

use MikeRow\Bandano\BananoTool;

$burn = BananoTool::string2burn('he11o', '1', '1');

var_dump($burn);
