<?php

	namespace apf\iface\type{

		interface Common{

			public static function instance($parameters=NULL);
			public static function cast($value,$parameters=NULL);

			//Internally in some ocassions we need the pure value rather than the 
			//toString value, such as in adding an item to a Vector Type
			public function valueOf();

			public function export();
			public function dump();
			public function __debugInfo();

		}

	}
