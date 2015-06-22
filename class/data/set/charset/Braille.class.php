<?php

	namespace apf\data\set\charset{

		use apf\data\set\Charset;
		use apf\type\base\Str											as	StringType;
		use apf\type\base\Char											as	CharType;
		use apf\type\util\base\Str										as	StringUtil;
		use apf\type\util\common\Variable							as	VarUtil;
		use apf\type\parser\Parameter									as	ParameterParser;
		use apf\data\set\charset\exception\UndefinedCharacter	as	UndefinedCharacterException;

		class Braille extends Charset{

			public static function convert($str,$parameters=NULL){

				$var				=	VarUtil::printVar($str,$parameters);
				$parameters		=	ParameterParser::parse($parameters);
				$ignoreUnknown	=	$parameters->find('ignoreUnknown',FALSE)->toBoolean()->valueOf();
				$strAsArray		=	StringUtil::toArray($str);
				$charset			=	parent::fetch("braille");
				$braille			=	StringType::instance();

				foreach($strAsArray as $v){

					if(!$ignoreUnknown&&!$charset->offsetExists($v->toLower())){

						$msg	=	"Character $v is not defined in charset. If you'd like to skip unknown";
						$msg	=	sprintf('%s %s',$msg,"characters please set the ignoreUnknown parameter");
						$msg	=	sprintf('%s %s',$msg,"to TRUE");

						throw new UndefinedCharacterException($msg);

					}

					if($v->isUppercase()){

						$braille[]	=	CharType::cast($charset["caps"]);

					}

					$braille[]	=	CharType::cast($charset[$v->toLower()->valueOf()]);

				}
	
				return StringType::cast($braille);

			}

			public static function decode($str,$parameters=NULL){

				$var				=	VarUtil::printVar($str,$parameters);
				$parameters		=	ParameterParser::parse($parameters);
				$ignoreUnknown	=	$parameters->find('ignoreUnknown',FALSE)->toBoolean()->valueOf();
				$strAsArray		=	StringUtil::toArray($str);
				$charset			=	parent::fetch("braille");
				$regularText	=	Array();
				$capsOn			=	FALSE;

				foreach($strAsArray as $v){

					$index	=	$charset->indexOf($v);

					if($index==="caps"){

						$capsOn	=	TRUE;
						continue;

					}

					if(!$ignoreUnknown&&!$charset->find($v->valueOf())){

						$msg	=	"Character $v is not defined in charset. If you'd like to skip unknown";
						$msg	=	sprintf('%s %s',$msg,"characters please set the ignoreUnknown parameter");
						$msg	=	sprintf('%s %s',$msg,"to TRUE");

						throw new UndefinedCharacterException($msg);

					}

					$regularText[]	=	$capsOn	?	StringUtil::toUpper($index)	:	$index;

					$capsOn	=	FALSE;

				}
	
				return StringType::cast(implode('',$regularText));

			}

		}

	}
