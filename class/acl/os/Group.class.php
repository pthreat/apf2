<?php

	namespace apf\acl\os{

		use apf\core\OS;
		use apf\core\Alias;

		$original	=	sprintf('apf\acl\os\%s\Group',OS::getInstance()->getFamily());
		$alias		=	'apf\acl\os\Group';

		Alias::define($original,$alias);

	}
