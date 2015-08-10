<?php

	ini_set('display_errors','On');
	error_reporting(E_ALL);

	require "class/core/Kernel.class.php";

	\apf\core\Kernel::boot(['loglevel'=>3,'logstdout'=>TRUE]);

