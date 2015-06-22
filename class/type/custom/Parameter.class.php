<?php

	/**
	* The option type sorts the inconvenience of dealing with string values 
	* or array values for method options.
	* Example:
	* function test($option){
	*	if(is_array ... if(is_string ...		
	* }
	*/

	namespace apf\type\custom{

		use apf\type\Common;

		use apf\type\base\util\Str								as	StringUtil;
		use apf\type\base\util\Vector							as	VectorUtil;
		use apf\type\util\common\Variable					as	VarUtil;
		use apf\type\util\common\Class_						as	ClassUtil;

		use apf\type\base\Str									as	StringType;
		use apf\type\base\IntNum								as	IntType;
		use apf\type\base\RealNum								as	RealType;
		use apf\type\base\Vector								as	VectorType;
		use apf\type\base\Char									as	CharType;
		use apf\type\base\Boolean								as	BooleanType;

		use apf\type\parser\Parameter							as	ParameterParser;

		use apf\type\base\validate\Str						as	ValidateString;
		use apf\type\base\validate\Variable					as	ValidateVar;
		use apf\type\base\validate\Type						as	ValidateType;

		use apf\iface\type\Common								as	TypeInterface;
		use apf\iface\type\Convertible						as	ConvertibleInterface;

		use apf\type\exception\Uncastable					as	UncastableException;
		use apf\type\custom\exception\ParameterNotFound	as	OptionNotFoundException;

		class Parameter extends Common{

			private	$name		=	NULL;

			protected function __construct($val){

				$this->value	=	$val;	

			}

			//ALWAYS returns an option collection
			//This will AVOID checking for what's set or whats not set
			//If an option is not there you can say $option->find('optionName')
			//And that's about it.

			public static function cast($name,$value=NULL){

				if(is_array($name)){

					$key		=	key($name);
					$value	=	$name[$key];
					$name		=	$key;

				}

				$opt	=	new static(NULL);

				$opt->setName($name);
				$opt->setValue($value);

				return $opt;

			}

			public static function instance($options=NULL){

				return new static(NULL,$options);

			}

			public function find(){

				return ParameterParser::parse($this->value);

			}

			public function setName($name){

				$this->name	=	$name;
				return $this;

			}

			public function getName(){

				return $this->name;

			}

			public function setValue($val){

				$this->value	=	$val;
				return $this;

			}

			public function &getValue(){

				return $this->value;

			}

			public function getArray(){

				return [$this->name=>$this->value];

			}

			public function toArray($parameters=NULL){

				return VectorType::cast([$this->name=>$this->value],$parameters);

			}

			public function toBoolean(){

				return BooleanType::cast($this->value);

			}

			public function valueOf(){

				return $this->value;

			}

			public function toObject(){

				return $this;

			}

			public function toInt($parameters=NULL){

				return IntType::cast($this->value,$parameters);

			}

			public function toString($parameters=NULL){

				return StringType::cast($this->value,$parameters);

			}

			public function toChar($parameters=NULL){

				return CharType::cast($this->value,$parameters);

			}

			public function toJSON(){
			}

			public function __toString(){

				return "{$this->toString()->valueOf()}";

			}

		}

	}
