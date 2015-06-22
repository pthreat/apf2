<?php

	namespace apf\core{

		class Cmd{

			use apf\validate\Str	as ValidateString;

			public static function searchOpt($option){

				foreach($_SERVER["argv"] as $opt=>$value){

					if(strtolower($value)!=strtolower($option)){
						continue;
					}

					if(isset($_SERVER["argv"][$opt+1])){

						if (!preg_match("/\-/",$_SERVER["argv"][$opt+1])){
							return $_SERVER["argv"][$opt+1];
						}

					}

					return '&';

				}

				return FALSE;

			}

			public static function select(Array $options,$prompt='SELECT>',\apf\core\Log $log=NULL){

				ValidateString::mustBeString($prompt,'Given prompt must be a string');

				if(is_null($log)){

					$log	=	new \apf\core\Log();

				}

				while(TRUE){

					foreach($options as $opt){

						$log->info(" $opt",0,"light_cyan");

					}

					$selected	=	strtolower(trim(self::readInput($prompt,$log),"\r\n"));

					foreach($options as $opt){

						if(strtolower($opt)==$selected){
							return $opt;	
						}

					}



				}

			}

			public static function yesNo($msg,\apf\core\Log $log=NULL){

				ValidateString::mustBeString($msg);

				if(is_null($log)){

					$log	=	new \apf\core\Log();

				}

				$msg		=	sprintf('%s (y/n):',$msg);
				$options	=	['y','yes','ya','ye','yeah','yep','n','no','nope'];
				$hasEcho	=	$log->getEcho();

				if($hasEcho){

					$log->setEcho(FALSE);

				}

				$select	=	substr(self::select($options,$msg,$log),0,1);

				if($hasEcho){

					$log->setEcho(TRUE);

				}

				return $select=="y";

			}

			public static function readInput($prompt=NULL,\apf\core\Log $log=NULL){

				if(!is_null($prompt)){

					if(is_null($log)){

						$log	=	new \apf\core\Log();

					}

					$echo	=	$log->getEcho();

					if(!$echo){

						$log->setEcho(TRUE);

					}

					$log->setNoLf();
					$log->setNoPrefix();
					$log->info($prompt);

					if(!$echo){
						$log->setEcho(FALSE);
					}

				}

				$fp	=	fopen("/dev/stdin",'r');
				$ret	=	fgets($fp,1024);

				fclose($fp);

				if(!is_null($log)){

					$log->usePrefix();
					$log->setLf();

				}

				return trim($ret);
	
			}

			public static function readWhileEmpty($prompt=NULL,\apf\core\Log $log=NULL){

				while(TRUE){

					$input	=	preg_replace("/[\r\n]/",'',self::readInput($prompt,$log));

					if(!empty($input)){

						return $input;

					}

				}

			}

		}

	}

