<?php

	namespace apf\io\util{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\util\Directory',OS::getInstance()->getFamily());
		$alias		=	'apf\io\util\Directory';

		Alias::define($original,$alias);

	}

