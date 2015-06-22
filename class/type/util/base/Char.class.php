<?php

	namespace apf\type\util\base{

		use apf\type\base\IntNum		as	IntType;
		use apf\type\base\Char			as	CharType;
		use apf\type\util\base\Str		as	StringUtil;
		use apf\type\util\base\IntNUm	as	IntUtil;
		use apf\type\parser\Parameter	as	ParameterParser;

		class Char extends Common{

			public static function ord($char){

				$encoding	=	mb_detect_encoding($char);

				if($encoding=='ASCII'){

					return IntType::cast(ord($char));

				}

				$ord	=	unpack('N', mb_convert_encoding($char,'UCS-4BE',$encoding));
				$ord	=	implode($ord);

				return IntType::cast($ord);

			}

			public static function toBinary($char,$parameters=NULL){

				return IntUtil::toBinary(self::ord($char,$parameters)->valueOf(),$parameters);

			}

			public static function chr($int,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('type','\apf\type\base\Char');
				$int			=	IntType::cast($int);
				$chr			=	mb_convert_encoding(sprintf('&#%d;',$int->valueOf()),'UTF-8','HTML-ENTITIES');
				return parent::returnValue($chr,$parameters);

			}

			public static function toBraille($char){

				$brailleMap = DataSet::fetch('braille');

			}

		}

	}
