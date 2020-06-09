<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$array = [34, 83, 255, 255, 90, 39, 02, 98];

var_dump(NanoTool::arr2bin($array));
