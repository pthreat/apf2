<?php

	namespace apf\type\util\common{

		use apf\type\base\Str								as	StringType;
		use apf\type\base\Vector							as	VectorType;
		use apf\type\parser\Parameter						as	ParameterParser;
		use apf\type\util\base\Vector						as	VectorUtil;
		use apf\type\util\base\Str							as	StringUtil;
		use apf\type\util\common\Obj						as	ObjUtil;
		use apf\type\validate\common\Obj					as	ValidateObj;
		use apf\type\validate\common\Variable			as	ValidateVar;

		use apf\util\convert\meassure\Byte				as	ByteMeassureConverter;

		use apf\type\exception\common\Unprintable		as	UnprintableException;
		use apf\type\exception\common\Untraversable	as	UntraversableException;

		class Variable{

			/**
			*Method			:	printVar
			*Description	:	Makes a string with almost any kind of variable you pass to it
			*
			*						NOTE: This method will NOT return an Apollo string type
			*						it will return a primitive string.
			*						You CANT cast to an Apollo type in this method, since 
			*						other types use printVar and if you do it will cause ***recursion***
			*
			*@param mixed $mixed Variable to be printed
			*@return string the printed value
			*
			*
			*NOTE: Should we rename this method "toScalar"
			*/

			public static function printVar($mixed,$parameters=NULL){

				$type			=	strtolower(gettype($mixed));
				$parameters	=	ParameterParser::parse($parameters);

				//if it doesn't finds to encoding, utf-8 will be assumed
				$parameters->findInsert('toEncoding','UTF-8');

				switch($type){

					case 'integer':
					case 'double':
						return $mixed;
					break;
					case 'null':
					case 'boolean':
						return (string)$mixed;
					break;
					case 'string':
						return StringUtil::encode($mixed,$parameters);
					break;

					case 'resource':
						$data	=	\stream_get_meta_data($mixed);
						return StringUtil::encode($data['uri'],$parameters);
					break;

					case 'array':

						return VectorType::cast($mixed,$parameters)->toString()->valueOf();

					break;

					case 'object':

						if(ValidateObj::isPrintable($mixed)){

							return ObjUtil::printObj($mixed,$parameters);

						}

						//If is traversable, make it a Vector and cast it to string

						if(ValidateVar::isTraversable($mixed)){

							return VectorType::cast($mixed)->implode($parameters);

						}

						return StringUtil::encode(serialize($mixed),$parameters);

					break;

				}

				throw new UnprintableException("Can not print variable of type $type");

			}

			public static function getSize(&$var,$parameters=NULL){
				
				$parameters	=	ParameterParser::parse($parameters);

				$parameters->findInsert('in','byte');

				$startMem	=	memory_get_usage();
				$var			=	unserialize(serialize($var));
				$endMem		=	memory_get_usage();

				if(self::printVar($parameters->find('in')->valueOf())=='byte'){

					return $endMem - $startMem;

				}

				$parameters->findInsert('from','byte');
				$parameters->replace('to',$parameters->find('in'));

				return ByteMeassureConverter::convert($endMem-$startMem,$parameters);

			}

			public static function traverse($var){

				if(!ValidateVar::isTraversable($var)){

					$type	=	gettype($var);
					throw new UnTraversableException("Can not traverse variable of type $type");

				}

				if(is_array($var)){

					return $var;

				}

				$return	=	Array();

				foreach($var as $k=>$v){

					$return[$k]	=	$v;

				}

				return $return;

			}

		}

	}
