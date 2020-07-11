<?php

require_once __DIR__ . '/../autoload.php';

use mikerow\php4nano\NanoTool;

var_dump(NanoTool::keys());

var_dump(NanoTool::keys(true));
