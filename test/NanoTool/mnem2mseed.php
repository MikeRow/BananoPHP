<?php

require __DIR__ . '/../autoload.php';

use mikerow\php4nano\NanoTool;

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

var_dump(NanoTool::mnem2mseed($mnem));
