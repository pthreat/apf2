<?php

	namespace apf\io{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\File',OS::getInstance()->getFamily());
		$alias		=	'apf\io\File';

		Alias::define($original,$alias);

	}

