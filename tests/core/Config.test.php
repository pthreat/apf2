<?php

	require "tests/include.php";

	use apf\core\Config;
	use apf\core\config\adapter\Ini	as	ConfigIni;

	$cfg	=	new Config(['a'=>'b']);
	echo $cfg->a."\n";

	$cfg	=	new ConfigIni('config/project.ini');
	echo $cfg->facebook->appPerms->{0}."\n";
