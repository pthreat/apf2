<?php

	namespace apf\io\util{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\util\File',OS::getInstance()->getFamily());
		$alias		=	'apf\io\util\File';

		Alias::define($original,$alias);

	}

