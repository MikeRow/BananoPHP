<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

var_dump(NanoTool::keys());

var_dump(NanoTool::keys(true));
