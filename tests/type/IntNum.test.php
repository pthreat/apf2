<?php

	ini_set("display_errors","On");
	error_reporting(E_ALL);

	require "tests/include.php";

	use apf\core\Log				as	Log;
	use apf\type\base\IntNum	as	IntType;
	use apf\type\util\base\Str	as	StringUtil;

	$log	=	new Log();
	$log->setNoPrefix();

	$num		=	'１２３４５６７８９❶';
	$log->log("Test $num");
	$int		=	IntType::cast($num);
	$log->log("Result: $int");
	$log->log("$int to Array: ");
	$intArray	=	$int->toArray();
	$log->log("Result: $intArray");

	$log->log("Try adding an erroneous element");

	$intArray->add('a');

	foreach($intArray as $int){

		$log->info("$int to binary: %s",$int->toBinary());
		$log->info("$int to hex: %s",$int->toHex());
		$log->info("$int to octal: %s",$int->toOctal());

	}

	$log->log("Add a correct element to the integer collection");

	$intArray->merge("111abcd11",['a','b','c','9']);

	$log->log("The collection now contains: $intArray");
