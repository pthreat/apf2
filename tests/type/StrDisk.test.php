<?php

	require "tests/include.php";

	use apf\type\base\StrDisk;
	use apf\type\util\common\Variable	as	VarUtil;

	$string	=	'あaaa';
	file_put_contents('/tmp/test',$string);
	$fp	=	fopen('/tmp/test','r');
	echo fread($fp,4);
	fclose($fp);
	$str		=	new StrDisk($string);
	echo $str."\n";
	var_dump($str[0]);
	die();

	foreach($str as $s){
		echo $s."\n";
	}

	echo $str."\n";
	die();

	$test		=	new \SPLFileObject('/tmp/test');
	$handler	=	$test->openFile('w');
	$handler->fwrite('123');
	$handler->fseek(0);
	$handler->fwrite(4);

	unset($test);
	echo file_get_contents('/tmp/test')."\n";
