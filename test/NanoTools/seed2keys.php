<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

list($seed) = NanoTools::keys();

var_dump(NanoTools::seed2keys($seed, 0));

var_dump(NanoTools::seed2keys($seed, 0, true));
