<?php

	namespace apf\iface\type\base{

		interface Vector extends \ArrayAccess,\Iterator{

			public function shuffle();
			public function shift();
			public function pad($value,$parameters=NULL);
			public function flip();
			public function pop();
			public function reverse();
			public function natSort();
			public function sort($parameters=NULL);
			public function rsort();
			public function asort();

		}

	}
