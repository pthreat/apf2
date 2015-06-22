<?php

	namespace apf\type\base{

		use apf\type\Common;

		use apf\type\util\base\Str							as	StringUtil;
		use apf\type\util\base\IntNum						as	IntUtil;
		use apf\type\util\common\Variable				as	VarUtil;

		use apf\type\validate\common\Variable			as	ValidateVar;

		use apf\type\collection\base\IntNum				as	IntCollection;

		use apf\type\exception\common\Uncastable		as	UncastableException;
		use apf\type\exception\base\intnum\MaxLimit	as	MaxLimitException;

		class IntNum extends Common{

			public function __construct($val,$parameters=NULL){

				$val	=	(int)$val;

				if($val>\PHP_INT_MAX){

					throw new MaxLimitException(sprintf("$val exceeds the PHP limit: %d",\PHP_INT_MAX));

				}

				return parent::__construct($val,$parameters);

			}

			public static function cast($val,$parameters=NULL){

				if(is_a($val,__CLASS__)){

					return $val;

				}

				if(is_int($val)){

					return new static($val,$parameters);

				}

				//If it's an array or a traversable object
				if(ValidateVar::isTraversable($val)){

					//traverse the object
					$val	=	VarUtil::traverse($val);

					//return the object's item count as an integer
					return new static(count($val),$parameters);

				}

				//Check if there are utf8 encoded numbers such as １２３
				$testInt	=	(int)VarUtil::printVar($val,['toEncoding'=>'ASCII//TRANSLIT']);

				//If the value is numeric, cast it to an integer
				if($testInt>0){

					return new static($testInt,$parameters);

				}

				if(is_object($val)){

					$val	=	count($val);

				}

				return new static($val,$parameters);

			}

			public static function instance($parameters=NULL){

				return self::cast(0,$parameters);

			}

			public function toOctal(){

				return IntUtil::toOctal($this->value,$this->parameters);

			}

			public function toHex(){

				return IntUtil::toHex($this->value,$this->parameters);

			}

			public function toBinary(){

				return IntUtil::toBinary($this->value,$this->parameters);

			}

			public function valueOf(){

				return (int)$this->value;

			}

			public function length(){

				return StringUtil::length($this->value);

			}

			public function toArray(){

				return IntUtil::toArray($this->value,$this->parameters);

			}

			public function toChar(){

				return CharType::cast($this->value);

			}

			public function __toString(){

				return (string)$this->value;

			}

		}

	}

