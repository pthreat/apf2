<?php

	namespace apf\type\base{

		use apf\type\Common;

		use apf\type\util\common\Class_							as	ClassUtil;

		use apf\type\custom\collection\Parameter				as	ParameterParser;

		use apf\type\exception\base\boolean\NotABoolean		as	NotABooleanException;
		use apf\type\exception\common\Uncastable				as	UncastableException;

		class Boolean extends Common{

			public static function cast($value,$parameters=NULL){

				$type			=	strtolower(gettype($value));

				switch($type){

					case	'int':
					case	'string':
					case	'array':
					case	'null':
					case	'boolean':
						return new static((boolean)$value);
					break;
					
					case	'object':

						if(ClassUtil::hasMethod(get_class($value),'toBoolean')){

							$value	=	$value->toBoolean();

							if(!is_bool($value)){

								if($options->find('throw')){

									throw new NotABooleanException('Returned argument by toBoolean is not a boolean value');

								}

								return -1;

							}

							return new static($value);

						}

						if($options["throw"]){

							throw new UncastableException("Can not cast this object to Boolean because it doesnt have a \"toBoolean\" method");

						}

						return -1;

					break;

				}

				if($options["throw"]){

					throw new UncastableException("Can not cast \"$type\" to boolean");

				}

				return -1;

			}

			public static function instance($options=NULL){

				return self::cast(FALSE,$options);

			}

			public function toArray(){

				return new Vector(["value"=>$this->value]);

			}

			public function toObject($parameters=NULL){

				$stdClass = new \stdClass();
				return $stdClass->value=$this->value;

			}

			public function toBoolean($parameters=NULL){

				return $this->value;

			}

			public function toInt($parameters=NULL){

				return new IntNum($this->value,$options);

			}

			public function toString($parameters=NULL){

				return Str::cast($this->value	?	'TRUE'	:	'FALSE');

			}

			public function toJSON(){
			}

			public function toChar(){

				return CharType::cast($this->value);

			}

			public function __toString(){

				return $this->toString();

			}

		}

	}

