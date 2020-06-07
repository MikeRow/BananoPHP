<?php

require_once __DIR__ . '/../../lib/NanoTool.php';

use php4nano\NanoTool as NanoTool;

var_dump(NanoTool::keys());

var_dump(NanoTool::keys(true));
