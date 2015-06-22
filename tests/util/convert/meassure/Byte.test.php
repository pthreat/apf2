<?php

	require "tests/include.php";

	use apf\core\Log;	
	use apf\util\convert\meassure\Byte as ByteMeassureConvert;

	$log			=	new Log();

	$meassure	=	1;

	$log->info("Number to convert $meassure");
	echo $meassure."\n";
	echo ByteMeassureConvert::convert((int)$meassure,['from'=>'megabyte','to'=>'byte'])."\n";
