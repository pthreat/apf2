<?php

	namespace apf\type\parser{

		use apf\type\util\common\Variable			as	VarUtil;
		use apf\type\validate\common\Variable		as	ValidateVar;
		use apf\type\custom\Parameter					as	ParameterType;

		use apf\type\base\Str							as	StringType;
		use apf\type\base\Boolean						as	BooleanType;
		use apf\type\base\Vector						as	VectorType;
		use apf\type\base\IntNum						as	IntType;

		use apf\type\collection\custom\Parameter	as	ParameterCollection;

		class Parameter{

			private function __construct(){
			}

			public static function parse($parameters=NULL,$defaultKey=NULL){

				if($parameters===NULL){

					return new ParameterCollection();

				}

				if($parameters instanceof ParameterType){

					$collection	=	new ParameterCollection();
					$collection->add($parameters);

					return $collection;

				}

				if($parameters instanceof ParameterCollection){

					return $parameters;

				}

				//This is in case $parameters is a string
				if(is_scalar($parameters)&&is_scalar($defaultKey)){

					$parameters	=	[$defaultKey=>$parameters];

				}

				if(is_array($parameters)){

					return self::fromArray($parameters);

				}

				if(ValidateVar::isTraversable($parameters)){

					return self::fromArray(VarUtil::traverse($parameters));

				}

				if(is_string($parameters)){

					//Attempt to convert to UTF-8
					//We can't use the string utilities here since they use parameters, thus
					//it would cause recursion.

					$parameters	=	self::stringToUTF8($parameters);
					$json			=	json_decode($parameters,$asArray=TRUE);

					if(!is_null($json)){

						return self::fromArray($json);

					}

					//Try HTTP Query Format
					$parseStr	=	'';

					parse_str($parameters,$parseStr);

					if(!empty($parseStr)){

						return self::fromArray($parseStr);

					}

				}

				if(!is_scalar($parameters)){

					throw new \InvalidArgumentException("Parameters are meant to be of scalar type");

				}

				return self::fromArray([$parameters=>NULL]);

			}

			//Meant to be used in conjunction with func_get_args
			public static function fromArgv(){

				$argv			=	func_get_args();
				$collection	=	new ParameterCollection();

				foreach($argv as $arg){
		
					if(empty($arg)){
						continue;
					}

					if(sizeof($arg)>1){

						foreach($arg as $k=>$v){

							$collection->add(ParameterType::cast($k,$v));

						}

						continue;

					}

					$value	=	self::findArrayKeyAndValue($arg);
					$collection->add(ParameterType::cast($value));

				}

				return $collection;

			}

			private static function findArrayKeyAndValue($array){

				foreach($array as $k=>$v){

					if(is_array($v)){

						return self::findArrayKeyAndValue($v);

					}

					return [$k=>$v];

				}

			}


			private static function stringToUTF8($string){

				$order		=	['UTF-8','UTF-7','ASCII','EUC-JP','SJIS','eucJP-win','SJIS-win','JIS','ISO-2022-JP'];

				mb_detect_order($order);

				$fromEncoding	=	mb_detect_encoding($string,$order,$strict=TRUE);
				$string			=	@iconv($fromEncoding,$toEncoding,$string);

				if($string===FALSE){

					$msg	=	"Given parameter string could not be encoded to UTF-8";
					$msg	=	sprintf("%s, consider using an array for parameters instead",$msg);

					throw new \InvalidArgumentException($msg);

				}

				return $string;

			}

			public static function fromArray(Array $parameters){

				$obj	=	new ParameterCollection();

				foreach($parameters as $name=>$value){

					$param	=	ParameterType::cast($name,$value);
					$obj->add($param);

				}

				return $obj;

			}

		}

	}

