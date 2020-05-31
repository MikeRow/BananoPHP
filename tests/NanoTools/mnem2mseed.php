<?php

require_once __DIR__ . '/../../lib/NanoTools.php';

use php4nano\NanoTools as NanoTools;

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

var_dump(NanoTools::mnem2mseed($mnem));
