<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

print_r(NanoTools::keys());

print_r(NanoTools::keys(true));
