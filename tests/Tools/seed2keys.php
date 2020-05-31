<?php

require_once __DIR__ . '/../../src/Tools.php';

use php4nano\Tools as NanoTools;

list($seed) = NanoTools::keys();

print_r(NanoTools::seed2keys($seed, 0));

print_r(NanoTools::seed2keys($seed, 0, true));
