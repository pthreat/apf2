<?php

	namespace apf\type\util\base{

		use apf\type\util\base\Str				as	StringUtil;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\collection\base\IntNum	as	IntCollection;
		use apf\type\parser\Parameter			as	ParameterParser;

		class IntNum{

			public static function toArray($value,$parameters=NULL){

				$value		=	(int)VarUtil::printVar($value,['toEncoding'=>'ASCII//TRANSLIT']);
				$intArray	=	VarUtil::traverse(StringUtil::toArray($value));

				foreach($intArray as &$int){

					$int	=	(int)$int;

				}

				return new IntCollection($intArray);

			}

			public static function toOctal($value,$parameters=NULL){

				return decoct((int)VarUtil::printVar($value,$parameters));

			}

			public static function toHex($value,$parameters=NULL){

				return dechex((int)VarUtil::printVar($value,$parameters));

			}

			public static function toBinary($value,$parameters=NULL){

				$value		=	decbin((int)VarUtil::printVar($value,$parameters));

				$parameters	=	ParameterParser::parse($parameters);
				$zeroPad		=	$parameters->find('zeroPad',8)->toInt()->valueOf();

				if(strlen($value)<$zeroPad){

					return str_pad($value,$zeroPad,0,\STR_PAD_LEFT);

				}

				return $value;

			}

		}

	}
