<?php

	namespace apf\io{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\Directory',OS::getInstance()->getFamily());
		$alias		=	'apf\io\Directory';

		Alias::define($original,$alias);

	}

