<?php

require_once __DIR__ . '/../../lib/NanoTool.php';

use php4nano\NanoTool as NanoTool;

$mseed = '8378D5EBE4EA73920CDDFE08FA6988533D2ED7380728F90DF1C386129B2E8CB5DA087B3A54CED9174C3B1555076AFFCD698E813711C0C41D12E4BA1BA92EE447';

var_dump(NanoTool::mseed2keys($mseed, 0));

var_dump(NanoTool::mseed2keys($mseed, 0, true));
