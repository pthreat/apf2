<?php

	namespace apf\type{

		use apf\core\Debug;

		use apf\iface\type\Common						as	TypeInterface;
		use apf\iface\Convertible						as	ConvertibleInterface;

		use apf\type\base\Boolean						as	BooleanType;
		use apf\type\base\IntNum						as	IntType;
		use apf\type\base\RealNum						as	RealType;
		use apf\type\base\Vector						as	VectorType;
		use apf\type\base\Str							as	StringType;

		use apf\type\util\base\Char					as	CharType;
		use apf\type\util\base\Str						as	StringUtil;
		use apf\type\util\base\Vector					as	VectorUtil;

		use apf\type\util\common\Class_				as	ClassUtil;
		use apf\type\util\common\Obj					as	ObjUtil;

		use apf\type\parser\Parameter					as	ParameterParser;

		use apf\type\validate\Variable				as	ValidateVar;

		use apf\type\exception\Uncastable			as	UncastableException;

		abstract class Common implements TypeInterface,ConvertibleInterface{

			use \apf\traits\type\parser\Parameter;

			protected	$value		=	NULL;

			protected function __construct($value=NULL,$parameters=NULL){

				$this->value	=	$value;
				$this->parseParameters($parameters);

			}

			public function getAPFType($withNS=FALSE){

				return $withNS ? get_class($this) : ClassUtil::removeNamespace(get_class($this));

			}

			public function getPHPType(){

				return self::__getPHPType($this->getAPFType());

			}

			protected static function __getAPFType($withNS=FALSE){

				return $withNS ? static::cast(get_called_class())	:	ClassUtil::removeNamespace(get_called_class());

			}

			protected static function __getPHPType($apfType=NULL){

				$type	=	StringType::cast(is_null($apfType) ? self::__getAPFType() : $apfType);

				switch($type->toLower()){

					case 'str':
						return 'string';
					break;

					case 'intnum':
						return 'int';
					break;

					case 'real':
						return 'double';
					break;

					case 'vector':
						return 'array';
					break;

				}

			}

			/**
			*Casts any primitive type to an Apollo type
			*/

			public final static function castAny($val,$options=NULL){

				$options	=	ParameterParser::parse($options);

				if($val instanceof TypeInterface){

					return $val;

				}

				$type				=	gettype($val);

				$types			=	[
											'integer'	=>	'IntNum',
											'string'		=>	'Str',
											'double'		=>	'RealNum',
											'array'		=>	'Vector',
											'boolean'	=>	'boolean',
											'object'		=>	'obj'
				];


				$isValidType	=	in_array($type,array_keys($types));

				if(!$isValidType){

					if($options["throw"]){

						throw new UncastableException("Can not cast \"$type\"");

					}

					return FALSE;

				}

				if($type=='string'&&StringUtil::length($val)->valueOf()==1){

					$types["string"]="Char";

				}

				$type		=	sprintf('\apf\type\base\%s',$types[$type]);

				return $type::cast($val,$options);

			}

			public function valueOf(){

				return $this->value;

			}

			public function copy(){

				return clone($this);

			}

			//This is not necessary in PHP 5.6.0 since there's __debugInfo
			public function dump(){

				return Debug::dump($this);	

			}

			//Only available in PHP 5.6.0
			//Meant to return an array

			public function __debugInfo(){

				return Debug::dump($this->value);

			}

			public function serialize(){

				return serialize($this);

			}

			public function equals($val){

				return serialize($this->value) == serialize($val);

			}

			public function export(){

				return var_export($this,TRUE);

			}

			/**********************************************
			*Basic casting to other types
			*/

			public function toInt($parameters=NULL){

				return IntType::cast($this->value,$parameters);

			}

			public function toReal($parameters=NULL){

				return RealType::cast($this->value,$parameters);

			}

			public function toString($parameters=NULL){

				return StringType::cast(sprintf('%s',$this->value));

			}

			public function toArray($parameters=NULL){

				return VectorType::cast($this->value,$parameters);

			}

			public function toBoolean($parameters=NULL){

				return BooleanType::cast($this->value,$parameters);

			}

			public function jsonSerialize(){

				return $this->value;

			}

			/*End of basic type casting
			**********************************************/

		}

	}
