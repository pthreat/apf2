<?php

	require "tests/include.php";
	use apf\core\OS;
	use apf\core\Log;
	use apf\type\util\base\Str	as	StringUtil;

	$log	=	new Log();
	$os	=	os::getInstance();
	$log->info($os->cpuinfo());
	$log->success($os->meminfo());
	$log->debug($os->partition('/'));
