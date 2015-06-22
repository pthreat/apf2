<?php

	namespace apf\acl\os{

		use apf\core\Alias;
		use apf\core\OS;

		$original	=	sprintf('apf\acl\os\%s\User',OS::getInstance()->getFamily());
		$alias		=	'apf\acl\os\User';

		Alias::define($original,$alias);

	}
