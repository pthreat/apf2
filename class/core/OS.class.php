<?php

	namespace apf\core{

		$original	=	sprintf('apf\core\os\%s',php_uname('s'));
		$alias		=	'apf\core\OS';

		if(!class_exists($original)){

			throw new \RuntimeException(sprintf('Unsupported platform %s',php_uname('s')));

		}

		Alias::define($original,$alias);

	}
