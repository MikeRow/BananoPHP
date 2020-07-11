<?php

require_once __DIR__ . '/../autoload.php';

use mikerow\php4nano\NanoTool;

list($seed) = NanoTool::keys();

var_dump($seed);

var_dump(NanoTool::seed2keys($seed, 0));

var_dump(NanoTool::seed2keys($seed, 0, true));
