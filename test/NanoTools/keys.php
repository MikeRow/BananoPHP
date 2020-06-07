<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

var_dump(NanoTools::keys());

var_dump(NanoTools::keys(true));
