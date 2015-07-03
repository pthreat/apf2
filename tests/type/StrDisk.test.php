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

	$log->info("Unset offset 1: $str[1]");

	unset($str[1]);

	$log->info("Value now is: $str");

	$log->info("Check if an existing value (2) isset: %s",var_export(isset($str[2]),TRUE));
	$log->info("Check if an unexisting value (22) isset: %s",var_export(isset($str[22]),TRUE));

	$log->info("Try to substring, starting from 0 to 3");
	$substr	=	$str->substr(0,2);
	$log->info("Substring value: $substr");
