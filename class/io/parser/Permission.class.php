<?php

	namespace apf\io\parser{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\parser\Permission',OS::getInstance()->getFamily());
		$alias		=	'apf\io\parser\Permission';

		Alias::define($original,$alias);

	}

