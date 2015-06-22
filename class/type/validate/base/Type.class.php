<?php

	namespace apf\validate{

		use apf\util\Obj		as	ObjUtil;
		use apf\util\Type		as	TypeUtil;
		use apf\util\Class_	as	ClassUtil;
		use apf\type\Str		as	StringType;

		class Type{

			public final static function isCollection($val,$gettype=FALSE){

				if(!is_object($val)){

					return FALSE;

				}

				$ns	=	ObjUtil::getNamespace($val)->valueOf();

				if($gettype){

					$noNs		=	ClassUtil::removeNamespace($val)->valueOf();
					return $ns=='apf\type\collection' ? $noNs	:	FALSE;

				}

				return $ns=='\apf\type\collection';

			}

			public final static function isOptionCollection($val){

				return self::isCollection($val,$gettype=TRUE)=='Option';

			}

			public final static function isAPF($val,$gettype=FALSE){

				if(!is_object($val)){

					return FALSE;

				}

				$ns	=	ObjUtil::getNamespace($val)->valueOf();

				if($gettype){

					$noNs		=	ClassUtil::removeNamespace($val)->valueOf();
					return $ns=='apf\type' ? $noNs	:	FALSE;

				}

				return $ns=='apf\type';

			}
				
			public static function isVector($val){

				return self::is('Vector',$val);

			}

			public static function isStr($val){

				return self::is('Str',$val);

			}

			public static function isIntNum($val){

				return self::is('IntNum',$val);

			}

			public static function isChar($val){

				return self::is('Char',$val);

			}

			public static function isOption($val){

				return self::is('Option',$val);

			}

			public static function is($type,$val){

				return self::isAPF($val,$gettype=TRUE);

			}

		}

	}
