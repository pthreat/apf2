<?php

	require "tests/include.php";

	use apf\io\Directory;
	use apf\core\Log;

	$log		=	new Log();
	$dir		=	'/home/kraken';
	$dir		=	Directory::instance($dir);
	echo $dir->find('.s');
