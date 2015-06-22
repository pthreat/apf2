<?php

	namespace apf\io\validate{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\io\os\%s\validate\File',OS::getInstance()->getFamily());
		$alias		=	'apf\io\validate\File';
		Alias::define($original,$alias);

	}

