<?php

	namespace apf\type\util\base{

		use apf\type\base\Base										as	Type;
		use apf\type\base\Vector									as	VectorType;
		use apf\type\base\IntNum									as	IntType;
		use apf\type\base\Str										as	StringType;

		use apf\type\parser\Parameter								as	ParameterParser;

		use apf\type\util\base\Str									as	StringUtil;
		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\util\common\Obj								as	ObjUtil;

		use apf\type\validate\common\Variable					as	ValidateVar;

		use apf\type\exception\base\vector\ValueNotFound	as	ValueNotFoundException;

		class Vector{

			/**
			*Object safe method to be able to compare objects inside of arrays
			*/

			public static function inArray($elm,$array){

				$array	=	VectorType::cast($array);

				if(!is_object($elm)){

					return in_array($elm,$array);

				}

				$elmType	=	gettype($elm);


				foreach($array as $value){

					$valType	=	gettype($value);

					switch($elmType){

						case 'object':

							if(ObjUtil::compare($elm,$value)){

								return TRUE;

							}

						break;

						default:

							if($elm===$value){

								return TRUE;

							}

						break;

					}

				}

			}

			public static function toStdClass($value,$parameters=NULL){

				$value		=	VarUtil::traverse($value);
				$parameters	=	ParameterParser::parse($parameters);

				$obj			=	$parameters->find('into',new \stdClass)->valueOf();

				if(!is_object($obj)){

					throw new \InvalidArgumentException("Argument \"into\" only accepts objects");

				}

				foreach($value as $k => $v) {

					if(!StringUtil::length($k)){

						continue;

					}

					if(ValidateVar::isTraversable($v)){

						$v			=	VarUtil::traverse($v);
						$obj->$k =	self::toStdClass($v);
						continue;

					}

					$obj->$k = $v;

				}

				return $obj;

			}

			public static function hasKeys(Array $value,Array $keys){

				$matched	=	Array();

				foreach($value as $key=>$value){

					if(in_array($key,$keys)){

						$matched[]	=	$key;

					}

				}

				return $matched;


			}

			public static function isVector($mixed){

				return is_array($mixed)||$mixed instanceof VectorType;

			}

			//Add an option for STRICT comparison finding a value
			//Make this option always true

			public static function find($array,$val){

				foreach($array as $v){

					if($v==$val){

						return $v;

					}

				}

			}

			public static function implode($array,$options=NULL){

				$options		=	ParameterParser::parse($options);
				$separator	=	$options->find('separator','')->toString()->valueOf();
				$separator	=	!is_null($separator)	?	VarUtil::printVar($separator)	:	'';

				$result		=	Array();

				if(is_object($array)&&ValidateVar::isTraversable($array)){

					$array	=	VarUtil::traverse($array);

				}

				foreach($array as $key=>&$val){
					
					if(ValidateVar::isTraversable($val)){

						$result[]	=	self::implode($val,$options);
						continue;

					}

					$result[] =	$val;

				}

				return StringType::cast(implode($separator,$result));

			}

			public static function walk(&$array,Callable $fn,$userData,$options=NULL){

				return array_walk($array,$fn,$userData);

			}

			public static function rWalk(&$array,Callable $fn,$parameters=NULL){

				static $recursion	=	0;
				$parameters			=	ParameterParser::parse($parameters);
				$levels				=	(int)$parameters->find('levels',NULL)->valueOf();

				self::walk($array,function(&$val,$key)use($fn,&$userData,$parameters,&$recursion,$levels){

					if(self::isVector($val)){

						$fn($val,$key,$userData);
						return self::rwalk($val,$fn,$userData,$parameters);

					}

					$recursion++;

					$fn($val,$key,$userData);

					if(!is_null($levels)&&$recursion>$levels){

						return $val;

					}

					return $val;

				},$userData);

				return $array;

			}

			public static function indexesOf($array,$value,$options=NULL){

				if(!is_null($options)){

					$options	=	ParameterParser::cast($options);

				}

				$value	=	StringType::cast($value);
				$vector	=	VectorType::instance();
				$found	=	FALSE;
				
				self::walk($array,function(&$val,$key) use (&$value,&$found,&$vector){

					if($value==$val){

						$found			=	TRUE;
						$vector[$key]	=	$val;

					}

				},$options);

				if(!$found){

					throw new ValueNotFoundException("Value \"$value\" was not found in this vector");

				}

				return $vector;

			}

			/**
			*Gets an index of this vector by a VALUE
			*@param mixed $index the index to search for
			*@return mixed The value at the specified index
			*/

			public static function indexOf($array,$value,$parameters=NULL){

				foreach($array as $key=>$val){

					if($val==$value){

						return $key;

					}

				}

				throw new ValueNotFoundException("value \"$value\" was not found in this vector");

			}

			public static function rIndexOf($array,$value,Array $options=Array()){

				$value	=	Type::castAny($value)->toString();
				$result	=	NULL;
				$found	=	FALSE;

				self::rwalk($array,function(&$val,$key) use (&$result,$value,&$found,&$options){

					if($value==$val&&$result===NULL){

						$found			=	TRUE;
						return $result =	Type::castAny($key);

					}

				},$options);

				if(!$found){

					throw new ValueNotFoundException("value \"$value\" was not found in this vector");

				}

				return $result;

			}

			/**
			*Gets the LAST index of this vector by an index
			*@param mixed $index the index to search for
			*@return mixed The value at the specified index
			*/

			public static function lastIndexOf(&$array,$value,Array $options=Array()){

				$value	=	Type::castAny($value)->toString();
				$result	=	NULL;
				$found	=	FALSE;

				self::walk($array,function(&$val,$key) use (&$result,$value,&$found,&$options){

					if($value==$val){

						$found			=	TRUE;
						return $result =	Type::castAny($key);

					}

				},$options);

				if(!$found){

					throw new ValueNotFoundException("value \"$value\" was not found in this vector");

				}

				return $result;

			}

			private static function returnValue($val,Array $options=Array()){

				if(array_key_exists('cast',$options)&&$options["cast"]===TRUE){

					return VectorType::cast($val,$options);

				}

				return $val;

			}

		}

	}

