<?php

	namespace apf\core{

		class Debug{

			public static function eDump(){

				foreach(func_get_args() as $var){

					var_dump($var);

				}

				die("-- END DUMP --\n");

			}

			public static function dump(){

				foreach(func_get_args() as $var){

					var_dump($var);

				}

			}

			public static function dumpToFile($file){

				$args	=	func_get_args();
				unset($args[0]);

				foreach($args as $arg){

					$data	=	sprintf("%s\n--------------------------\n",var_export($arg,TRUE));
					file_put_contents($file,$data,FILE_APPEND);	

				}

			}

		}

	}
