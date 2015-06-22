<?php

	require "tests/include.php";

	use apf\core\Log;
	use apf\data\set\Common	as	DataSet;
	use apf\data\set\charset\Braille;
	use apf\data\set\charset\Morse;
	use apf\type\util\base\Str	as	StringUtil;

	DataSet::setDatasetDir('data');

	$log		=	new Log();

	$test		="Hello world from Apollo Framework";
	$log->info("Test string $test");
	$braille	=	Braille::convert($test);
	$log->info("String to braille: $braille");
	$log->info("Decode from braille: %s",Braille::decode($braille));

	$morse	=	Morse::convert($test);
	$log->info("String to Morse: $morse");
	$log->info("Decode from Morse: %s",Morse::decode($morse));



