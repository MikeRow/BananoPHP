<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

list($seed) = NanoTools::keys();

print_r(NanoTools::seed2keys($seed, 0));

print_r(NanoTools::seed2keys($seed, 0, true));
