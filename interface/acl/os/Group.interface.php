<?php

	namespace apf\iface\acl\os{

		interface Group{

			public static function instance($val,$parameters=NULL);
			public function setGID($gid);
			public function getMembers();

		}

	}
