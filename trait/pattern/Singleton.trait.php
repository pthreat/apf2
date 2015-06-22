<?php

	namespace apf\traits\pattern{

		trait Singleton{

			protected static $instance	=	NULL;

			private function __construct(){
			}

			public function __clone(){

				throw(new \BadMethodCallException("Can't clone Singleton class"));

			}

			public static function getInstance(){

				if(!is_null(self::$instance)){

					return self::$instance;

				}

				return self::$instance	=	new static();

			}

			public static function __callStatic($method,$data){

				return call_user_func_array(array(self::$instance,$method),$data);

			}

		}

	}

