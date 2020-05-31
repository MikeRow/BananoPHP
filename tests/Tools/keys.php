<?php

require_once __DIR__ . '/../../lib/Tools.php';

use php4nano\Tools as NanoTools;

print_r(NanoTools::keys());

print_r(NanoTools::keys(true));
