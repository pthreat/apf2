<?php

	namespace apf\web\core{

		class Kernel extends \apf\core\Kernel{

			private static $dispatcher		=	NULL;

			/**
			*@var $documentRoot 
			*This variable specifies where your web root directory will be located
			*this basically means where your web server (Apache, NGinx, IIS, or wathever other)
			*is serving the index.php file for it to be displayed to the rest of the world.
			*/

			private static $documentRoot	=	NULL;

			public static function boot(Array $options=Array()){

				parent::boot($options);

				die();
				self::parseDocumentRoot('test');
				self::$dispatcher	=	new Dispatcher();

				if($dispatch){

					return self::$dispatcher->dispatch();

				}

			}

			private static function parseDocumentRoot($path,$config){
				
				self::$documentRoot	=	dirname($_SERVER["SCRIPT_FILENAME"]);

				//In windows $_SERVER["SCRIPT_FILENAME"] will be returned with slashes instead of back
				//slashes causing the following string manipulation via substring and strpos to fail
				//Thats why we make sure that $_SERVER["SCRIPT_FILENAME"] contains the proper slashes
				//for the given OS, sadly we have to do this with preg_replace.

				return self::$documentRoot	=	preg_replace('#/#',parent::$ds,self::$documentRoot);

			}


			public static function getDispatcher(){

				return self::$dispatcher;

			}

			/**
			*This method returns the Document Root 
			*@return String The Document Root Path
			*/

			public static function getDocumentRoot(){

				return self::$documentRoot;

			}

		}

	}

?>
