<?php
	
	namespace apf\iface\io\validate{

		interface File{

			 public static function isReadable($file,$parameters=NULL);
			 public static function isWritable($file,$parameters=NULL);
			 public static function exists($file,$parameters=NULL);

		}

	}
