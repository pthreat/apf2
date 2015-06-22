<?php

	namespace apf\iface\io\util{

		interface File{

			 public static function chmod($file,$parameters=NULL);
			 public static function getGroup($file,$parameters=NULL);
			 public static function getOwner($file,$parameters=NULL);
			 public static function create($dir,$parameters=NULL);
			 public static function delete($dir,$parameters=NULL);
			 public static function rename($dir,$parameters=NULL);
			 public static function copy($dir,$parameters=NULL);
			 public static function move($dir,$parameters=NULL);

		}

	}
