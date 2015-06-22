<?php

	require	"tests/include.php";

	use apf\parser\file\Ini	as	IniParser;

	$config	=	new IniParser('config/project.ini');
	var_dump($config->facebook->url);
