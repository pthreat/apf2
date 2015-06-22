<?php

	ini_set("display_errors","On");
	error_reporting(E_ALL);


	require "tests/include.php";

	use apf\core\Log			as	Log;
	use apf\type\base\Str	as	StringType;
	use apf\type\base\Char	as	CharType;

	$log	=	new Log();
	$log->setNoPrefix();

	$log->info('INFO: test');
	$log->success('SUCCESS: test');
	$log->warning('WARNING: test');
	$log->error('ERROR: test');
	$log->emergency('EMERGENCY: test');
	
	$log->setLogLevel(1);

	$log->info("This won't show up");
	$log->info("This will show up",['level'=>1]);
