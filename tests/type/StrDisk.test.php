<?php

	require "tests/include.php";

	use apf\type\base\StrDisk;
	use apf\type\util\common\Variable	as	VarUtil;
	use apf\core\Log;

	$log		=	new Log();

	$string	=	'aあ こんにちはaaakanaa';
	$log->info("Base string: $string");
	$str		=	new StrDisk($string);
	$log->info("Length: %d",$str->strlen());
	$log->info("Iterate through disk string");

	foreach($str as $key=>$s){

		echo $key.'=>'.$s."\n";

	}

	$log->info("String check: $str");
	$log->info("Replace offset 1: $str[1] with character 'b'");

	$str[1]	=	'b';

	$log->info("Modified string: $str");

