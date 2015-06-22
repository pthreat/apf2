<?php

	require "tests/include.php";

	use apf\type\base\Char			as CharType;
	use apf\type\util\base\Char	as	CharUtil;

	for($i=10240;$i<20000;$i++){

		echo "$i ".CharUtil::chr($i,['cast'=>FALSE])."\n";

	}


