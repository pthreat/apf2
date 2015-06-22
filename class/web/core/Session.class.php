<?php

	namespace apf\web\core{

		class Session{

			public static function &get($key=NULL){

				if(!array_key_exists($key,$_SESSION)){

					throw new \Exception("Invalid session key requested");

				}

				return $_SESSION[$key];

			}

			public static function destroy(){

				session_unset();
				session_destroy();

			}

			public static function set($key,$value){

				$_SESSION[$key]	=	$value;

			}

			public static function delete($key){

				if(!array_key_exists($key,$_SESSION)){

					throw new \Exception("Invalid session key requested (when removing)");

				}

				unset($_SESSION[$key]);

			}

		}

	}
