<?php

	namespace apf\type\validate\common{

		use apf\type\collection\custom\Parameter	as	ParameterType;

		use apf\type\util\common\Class_				as	ClassUtil;
		use apf\type\util\common\Obj					as	ObjUtil;

		use apf\type\validate\common\Variable		as	ValidateVar;
		use apf\type\validate\common\Obj			as	ValidateObj;

		class Variable{

			public static function isTraversable($val){

				if(Obj::isTraversable($val)){

					return TRUE;

				}

				if(is_array($val)){

					return TRUE;

				}

				return FALSE;

			}

			public static function isPrimitive($var){

				return gettype($var)!=='object';

			}

			public static function isAPF($var){

				if(!is_object($var)){

					return FALSE;

				}

				$ns	=	ObjUtil::getNamespace($var);
				$ns	=	substr($ns,0,strrpos($ns,'\\'));

				return $ns=='apf\type';

			}

			public static function isPrintable($val,$print=FALSE){

				if(is_string($val)){

					return $print	?	$val	:	TRUE;

				}

				return ValidateObj::isPrintable($val,$print);

			}

		}

	}
