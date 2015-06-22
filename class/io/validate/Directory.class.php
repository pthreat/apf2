<?php

	namespace apf\io\validate{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\validate\Directory',OS::getInstance()->getFamily());
		$alias		=	'apf\io\validate\Directory';

		Alias::define($original,$alias);

	}

