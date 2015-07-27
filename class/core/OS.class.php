<?php

	use apf\core\Alias;

	$platform	=	ucwords(php_uname('s'));

	$original	=	sprintf('\apf\core\os\%s',$platform);
	$alias		=	'apf\core\OS';

	if(!class_exists($original,$autoload=TRUE)){

		throw new \RuntimeException(sprintf('Unsupported platform %s',php_uname('s')));

	}

	class_alias($original,$alias,$autoload=TRUE);
