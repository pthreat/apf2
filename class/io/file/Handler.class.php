<?php

	namespace apf\io\file{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\file\Handler',OS::getInstance()->getFamily());
		$alias		=	'apf\io\file\Handler';

		Alias::define($original,$alias);

	}

