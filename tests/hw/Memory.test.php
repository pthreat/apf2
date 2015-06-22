<?php

	require "tests/include.php";

	use apf\core\OS;
	use apf\type\util\common\Variable	as	VarUtil;

	echo OS::getInstance()->memInfo();
