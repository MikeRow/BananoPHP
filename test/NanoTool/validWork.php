<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$hash       = 'A36B0B8CC84253E57C90E959755816EA51F00FA3497B8D2C665551FAECFBD0D0';
$work       = '6522FE6A235DAE7F';
$difficulty = 'ff00000000000000';

var_dump(NanoTool::validWork($hash, $work, $difficulty));
