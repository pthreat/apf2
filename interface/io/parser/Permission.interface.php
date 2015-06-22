<?php

	namespace apf\iface\io\parser{

		interface Permission{

			 public static function parse($file,$perms);
			 public static function fromFile($file);
			 public function getOwner();
			 public function getGroup();
			 public function getWorld();

		}

	}
