<?php

require_once __DIR__ . '/../../lib/NanoTool.php';

use php4nano\NanoTool as NanoTool;

$mnem = [
	'turkey',
	'fever',
	'wish',
	'tray',
	'remind',
	'abandon',
	'announce',
	'skin',
	'input',
	'permit',
	'mobile',
	'exclude',
	'ghost',
	'album',
	'floor',
	'utility',
	'attack',
	'oil',
	'payment',
	'stumble',
	'noise',
	'orbit',
	'grain',
	'dash'
];

var_dump(NanoTool::mnem2hex($mnem));
