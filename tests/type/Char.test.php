<?php

	require "tests/include.php";

	use apf\type\base\Char			as CharType;
	use apf\type\util\base\Char	as	CharUtil;

	$char	=	CharType::cast('こ');
	var_dump($char->isMultibyte());

	for($i=10240;$i<20000;$i++){

		echo "$i ".CharUtil::chr($i,['cast'=>FALSE])."\n";

	}


