<?php

require_once __DIR__ . '/../../lib/NanoTool.php';

use php4nano\NanoTool as NanoTool;

$array = [34, 83, 255, 255, 90, 39, 02, 98];

$binary = NanoTool::arr2bin($array);

var_dump(NanoTool::bin2arr($binary));
