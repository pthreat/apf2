<?php

	namespace apf\type\base{

		use apf\type\Common;

		use apf\type\util\base\Str				as	StringUtil;
		use apf\type\util\base\Char			as	CharUtil;
		use apf\type\parser\Parameter			as	ParameterParser;
		use apf\type\util\base\IntNUm			as	IntUtil;
		use apf\type\util\common\Class_		as	ClassUtil;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\validate\base\Str		as	ValidateString;

		class Char extends StrCommon{

			public static function cast($value,$parameters=NULL){

				if(is_a($value,__CLASS__)){

					return $value;

				}

				$parameters	=	ParameterParser::parse($parameters);

				if((boolean)$parameters->find('strict',FALSE)->valueOf()){

					if(StringUtil::length($value)===1){

						return new static($value,$parameters);

					}

					$value	=	StringUtil::substr($value,['start'=>0,'end'=>1,'cast'=>FALSE]);

					return new static($value,$parameters);

				}

				$value	=	VarUtil::printVar($value,$parameters);

				if(StringUtil::length($value)->valueOf()==1){

					return new static($value,$parameters);

				}

				if(is_numeric($value)){

					return CharUtil::chr((int)$value,['cast'=>FALSE]);

				}

				$value	=	StringUtil::substr($value,['start'=>0,'end'=>1,'cast'=>FALSE]);

				return new static($value);

			}

			public static function instance($parameters=NULL){

				return self::cast(NULL,$parameters);

			}

			public function isMultibyte(){

				return strlen($this->value)>1;

			}

			public function toBinary($parameters=NULL){

				return CharUtil::toBinary($this->value,$parameters);

			}

			public function ord(){

				return CharUtil::ord($this->value,$this->parameters);

			}

			public function toInt($parameters=NULL){

				return $this->ord();

			}

			public function toBoolean($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return BooleanType::cast($this->value,$parameters);

			}

			public function toChar(){

				return $this;

			}

		}

	}
