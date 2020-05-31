<?php

require_once __DIR__ . '/../../lib/Tools.php';

use php4nano\Tools as NanoTools;

$array = [ 34, 83, 255, 255, 90, 39, 02, 98 ];

echo NanoTools::arr2bin($array);
