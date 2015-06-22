<?php

	namespace apf\io\directory{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\directory\Handler',OS::getInstance()->getFamily());
		$alias		=	'apf\io\directory\Handler';

		Alias::define($original,$alias);

	}

