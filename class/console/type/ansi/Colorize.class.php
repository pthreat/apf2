<?php

	namespace apf\console\type\ansi{

		use apf\type\util\common\Variable		as	VarUtil;

		class Colorize{

			/**
			 * @var $colors Array Different colors for console output
			 */

			private static $colors = Array(
													"black"		=>"\33[0;30m",
													"blue"		=>"\33[0;34m",
													"lblue"		=>"\33[1;34m",
													"green"		=>"\33[0;32m",
													"lgreen"		=>"\33[1;32m",
													"cyan"		=>"\33[0;36m",
													"lcyan"		=>"\33[1;36m",
													"red"			=>"\33[0;31m",
													"lred"		=>"\33[0;31m",
													"purple"		=>"\33[0;35m",
													"lpurple"	=>"\33[1;35m",
													"brown"		=>"\33[0;33m",
													"gray"		=>"\33[1;30m",
													"lgray"		=>"\33[0;37m",
													"yellow"		=>"\33[1;33m",
													"white"		=>"\33[1;37m"
			);

			public static function text($string,$color){

				$color	=	VarUtil::printVar($color);
				$string	=	VarUtil::printVar($string);

				if(!array_key_exists($color,self::$colors)){

					$msg	=	"No such color: \"$color\". Available colors are: %s";
					$msg	=	sprintf(array_keys($this->colors));

					throw new \InvalidArgumentException($msg);

				}

				return sprintf('%s%s%s',self::$colors[$color],$string,"\033[37m");

			}

			public static function __callStatic($method,$args){

				return self::text($args,$method);

			}
			
		}

	}
