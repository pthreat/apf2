<?php

	//This class only accumulates aliased classes in order 
	//for them to be tracked in the future

	namespace apf\core{

		use apf\platform\Common 		as Platform;
		use apf\type\parser\Parameter	as	ParameterParser;

		class Alias{

			private static $aliases	=	Array();	

			public static function define($original,$alias){

				class_alias($original,$alias);
				self::$aliases[$alias]	=	$original;

			}

		}

	}
