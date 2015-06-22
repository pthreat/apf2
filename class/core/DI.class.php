<?php

	namespace apf\core{

		class DI{

			private static $container	=	Array();

			public static function &get($key){

				if(!self::exists($key)){

					throw new \Exception("Invalid DI key: $key");

				}

				return self::$container[$key]["value"];

			}

			public static function exists($key){

				return isset(self::$container[$key]);

			}

			public static function isReadOnly($key){

				if(!isset(self::$container[$key])){

					return FALSE;

				}

				return self::$container[$key]["readonly"];

			}

			public static function set($key,$value,$readOnly=FALSE){

				if(self::isReadOnly($key)){

					$msg = "Value \"$key\" has been marked as readonly and can not be changed";

					throw new \Exception($msg);

				}

				self::$container[$key]	=	Array(
															"value"		=>	$value,
															"readonly"	=>	(boolean)$readOnly
				);

			}

		}

	}

