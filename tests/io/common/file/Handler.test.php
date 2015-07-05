<?php

	require "tests/include.php";

	use apf\io\File as File;
	use apf\core\Log;

	$log		=	new Log();

	$log->info("Make a temporary file");
	$file		=	new File(['tmp'=>TRUE]);
	$handler	=	$file->getHandler(['mode'=>'a+']);
	$handler->fwrite('hello world あ');

	$handler->fseekChar(-1);
	var_dump($handler->fgetChar());



