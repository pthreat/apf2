<?php

	namespace apf\iface\data\set{

		interface Charset{

			public static function convert($val,$parameters=NULL);
			public static function decode($val,$parameters=NULL);

		}

	}	
