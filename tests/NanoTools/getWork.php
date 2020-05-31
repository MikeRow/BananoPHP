<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

$hash       = 'A36B0B8CC84253E57C90E959755816EA51F00FA3497B8D2C665551FAECFBD0D0';
$difficulty = 'ff00000000000000';

var_dump(NanoTools::getWork($hash, $difficulty));
