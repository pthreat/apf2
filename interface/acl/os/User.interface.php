<?php

	namespace apf\iface\acl\os{

		interface User{

			public static function instance($val,$parameters=NULL);
			public static function getCurrent();
			public function isRoot();
			public function setUID($uid);
			public function getGroup();
			public function getGroups($parameters=NULL);

		}

	}
