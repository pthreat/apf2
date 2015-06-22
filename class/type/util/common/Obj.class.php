<?php

	namespace apf\type\util\common{

		use apf\type\base\Vector					as	VectorType;
		use apf\type\util\common\Class_			as	ClassUtil;
		use apf\type\util\base\Str					as	StringUtil;
		use apf\type\validate\common\Variable	as	ValidateVar;
		use apf\type\validate\common\Obj			as	ValidateObj;
		use apf\exception\type\Unprintable		as	UnprintableException;

		class Obj{

			public static function compare($obj1,$obj2){

				return serialize($obj1) === serialize($obj2);

			}

			public static function getNamespace($val){

				if(!is_object($val)){

					return FALSE;

				}

				return ClassUtil::getNamespace(get_class($val));

			}

			public static function printObj($obj,$parameters=NULL){

				if(!is_object($obj)){

					throw new UnprintableException("Given argument is not an object");

				}

				$hasToString		=	ValidateObj::hasMethod($obj,'toString');
				$hasMagicToString	=	ValidateObj::hasMethod($obj,'__toString');

				//Magic to string has precedence over toString
				if($hasMagicToString){

					return StringUtil::encode("$obj",$parameters);

				}

				if($hasToString){

					$val	=	StringUtil::encode($obj->toString(),$parameters);

					if(ValidateVar::isAPF($val)||is_string($val)){

						return $val;

					}

					$msg	=	"Object must return an apollo type or a string";
					throw new \InvalidArgumentException($msg);

				}

				$class	=	gettype($class);
				$msg		=	"Can not print object of class $class, it doesn't has a toString ";
				$msg		=	sprintf('%s or a __toString magic method',$msg);

				throw new UnprintableException($msg);

			}

			public static function getAttributes($filters,Array $options=Array()){

				$options	=	['validTypes'=>['string','array']];

				$filters = Type::castAny($filters,$options);

			}

		}

	}
