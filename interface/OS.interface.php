<?php

	namespace apf\iface{

		interface OS{

			public static function getInstance();
			public function CPUInfo();
			public function MemInfo();
			public function Partition($name);

			//Allows to configure a certain platform 
			//from a certain configuration

			public function configure(Config $config);

		}

	}
