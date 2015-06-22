<?php

	namespace apf\iface\io{

		interface File{

			public function create($parameters=NULL);
			public function copy($parameters=NULL);
			public function chmod($perms);
			public function ls();
			public function getHandler();
			public function delete();

		}
	}

