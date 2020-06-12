<?php

require_once __DIR__ . '/../../autoload.php';

use php4nano\NanoTool as NanoTool;

$hash       = '584AD2078048C96373547627197ADCE066FB9F32722037A85C5D118649B21BB7';
$work       = '0c388975d5ea4495';
$difficulty = 'ffffffc000000000';

var_dump(NanoTool::validWork($hash, $work, $difficulty));
