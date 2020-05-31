<?php

require_once __DIR__ . '/../../src/Tools.php';

use php4nano\Tools as NanoTools;

echo NanoTools::raw2den('50000000000000000000000000000000000000', 'NANO');
