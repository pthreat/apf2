<?php

	namespace apf\type\collection\custom{

		use apf\type\base\Str										as	StringType;
		use apf\type\base\Boolean									as	BooleanType;
		use apf\type\base\Vector									as	VectorType;
		use apf\type\base\IntNum									as	IntType;
		use apf\type\base\RealNum									as	RealNum;

		use apf\type\custom\Parameter								as	ParameterType;

		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\validate\common\Variable					as	ValidateVar;

		use apf\type\parser\Parameter								as	ParameterParser;
		use apf\type\collection\custom\Parameter				as	ParameterCollection;
		use apf\type\exception\custom\parameter\NotFound	as	ParameterNotFoundException;
		use apf\type\exception\custom\parameter\NotInCase	as	ParameterNotInCaseException;

		class Parameter{

			private	$key		=	NULL;
			private	$value	=	Array();

			public function merge($param){

				if(is_null($param)){

					return;

				}

				if(!($param instanceof ParameterCollection)){

					$param	=	ParameterParser::parse($param);

				}

				$newValues	=	Array();

				foreach($this->value as &$item){

					foreach($param->value as $pitem){

						if($pitem->getName()==$item->getName()){

							$item	=	$pitem;
							continue;

						}

						$newValues[]	=	$pitem;

					}

				}

				foreach($newValues as $new){

					$this->add($new);

				}

				return $this;

			}

			private static function printVar($value){

				if(!ValidateVar::isTraversable($value)){

					return "$value";

				}

				if(!is_array($value)){

					$value	=	VarUtil::traverse($value);

				}

				return urldecode(http_build_query($value));

			}

			public function findParametersBeginningWith($str){

				$str					=	preg_quote(VarUtil::printVar($str),'/');
				$parametersLike	=	VectorType::instance();
				$values				=	$this->getValue();

				foreach($values as $item){

					if(preg_match("/^$str/",strtolower((string)$item->getName()))){

						$name	=	StringType::cast($item->getName())->substr(0,strlen($str));
						$parametersLike[$name]	=	$item->getValue();

					}

				}

				return ParameterParser::parse($parametersLike);

			}

			public function add(ParameterType $item){

				$this->value[]	=	$item;

			}

			public function &getValue(){

				return $this->value;

			}

			//Find a certain value, cast to boolean the value if found
			//Functionality: Avoids external boolean casting
			public function findBool($name,$default=NULL){

				$values	=	$this->getValue();

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item->setValue(BooleanType::cast($item->getValue())->valueOf());

					}

				}

				return ParameterType::cast($name,BooleanType::cast($default)->valueOf());

			}

			public function findPrint($name,$default=NULL){

				$values	=	$this->getValue();

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item->setValue(self::printVar($item->getValue()));

					}

				}

				return ParameterType::cast($name,self::printVar($default));

			}

			//Find a certain value, cast to IntNum the value if found
			//Functionality: Avoids external boolean casting
			public function findInt($name,$default=NULL){

				$values	=	$this->getValue();

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item->setValue(IntType::cast($item->getValue())->valueOf());

					}

				}

				return ParameterType::cast($name,IntType::cast($default)->valueOf());

			}

			public function findStr($name,$default=NULL){

				$values	=	$this->getValue();

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item->setValue(StringType::cast($item->getValue())->valueOf());

					}

				}

				return ParameterType::cast($name,StringType::cast($default)->valueOf());

			}

			public function findVector($name,$default=NULL){

				$values	=	$this->getValue();

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item->setValue(VectorType::cast($item->getValue())->valueOf());

					}

				}

				return ParameterType::cast($name,VectorType::cast($default)->valueOf());

			}

			public function setValue($value){
				$this->value	=	$value;
				return $this;
			}

			public function replace($name,$value){

				$values	=	&$this->getValue();

				if(empty($values)){

					$param	=	ParameterType::cast($name,$value);
					$this->add($param);
					return $param;

				}

				foreach($values as $key=>&$val){

					if($val->getName()!==$name){

						continue;	

					}

					$val	=	ParameterType::cast($name,$value);
					$this->setValue($values);

					return $val;

				}

				$param	=	ParameterType::cast($name,$value);
				$this->add($param);
				$this->setValue($values);

				return $param;

			}

			//Similar to replace, but if the parameter is found it will not replace it

			public function findInsert($name,$default){

				foreach($this->value as $key=>&$val){

					if($val->getName()===$name){

						return $val;

					}

				}

				$param	=	ParameterType::cast($name,$default);
				$this->add($param);

				return $param;

			}

			/**
			*Finds a parameter in a set of parameters
			*/

			public function findOneOf(){

				$args	=	func_get_args();

				if(empty($args)){

					throw new \InvalidArgumentException("You must provide something to find");

				}

				$args		=	is_array($args[0])	?	$args[0]	:	$args;
				$size		=	sizeof($args);
				$default	=	$size==1	?	NULL	:	$args[$size-1];

				foreach($args as $value){

					$value	=	$this->find($value);

					if(!is_null($value->getValue())){

						return $value;

					}

				}

				return ParameterType::cast($args[0],$default);

			}

			public function findOneValueOf(){

				$args	=	func_get_args();

				if(empty($args)){

					throw new \InvalidArgumentException("You must provide something to find");

				}

				$args		=	is_array($args[0])	?	$args[0]	:	$args;
				$size		=	sizeof($args);
				$default	=	$size==1	?	NULL	:	$args[$size-1];

				foreach($args as $value){

					$value	=	$this->findValue($value);

					if(!is_null($value->getValue())){

						return $value;

					}

				}

				return ParameterType::cast($args[0],$default);

			}

			/**
			*Method			:	findAliasedValue
			*Description	:	This method is very useful when you need to parse complex options
			*						such as having an option map with multiple aliased values.
			*/

			private function findAliasedValue($name,$supremeDefault=NULL,Array $aliases){

				$values	=	$this->getValue();

				foreach($aliases as $default=>$alias){

					foreach($alias as $a){

						foreach($values as $item){

							if($item->getName()!==$name){

								continue;

							}

							if($item->getValue()==$default||$item->getValue()==$a){

								return $default;

							}

						}

					}

				}

				return $supremeDefault;

			}

			public function findValue($value,$default=NULL){

				$values	=	$this->getValue();

				if(!ValidateVar::isTraversable($values)){

					return $default;

				}

				$value	=	self::printVar($value);

				foreach($values as $itemName=>$itemValue){

					$compareValue	=	self::printVar($itemValue);

					if(strtolower((string)$compareValue)===(strtolower((string)$value))){

						if(ValidateVar::isTraversable($itemValue)){

							$collection			=	new static($itemValue);
							$collection->key	=	$itemName;

							return $collection;

						}

						return ParameterType::cast($itemName,$itemValue);

					}

				}

				if(ValidateVar::isTraversable($default)){

					return new static($default);

				}

				return ParameterType::cast($default,$default);

			}

			public function demand($name){

				$values	=	$this->getValue();

				if(empty($values)){

					throw new ParameterNotFoundException("Parameter $name is required but is missing");

				}

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						return $item;

					}

				}

				throw new ParameterNotFoundException("Parameter $name is required but is missing");

			}

			public function selectCase($key,$possibleValues,$default=NULL){

				$value	=	is_null($default)	?	$this->demand($key)	:	$this->find($key);

				if(!$value->valueOf()&&!is_null($default)){

					return $default;

				}

				$hasDefault	=	!is_null($default);

				if($hasDefault&&!in_array($default,$possibleValues)){

					$msg	=	"DEFAULT parameter $key is invalid, must be one of: ";
					$msg	=	sprintf('%s %s',$msg,implode(',',$possibleValues));

					throw new ParameterNotIncaseException($msg);

				}

				if(!in_array($value->valueOf(),$possibleValues)){

					if($hasDefault){

						return ParameterType::cast($key,$default);

					}

					$msg	=	"Parameter \"$key\" is invalid, must be one of: ";
					$msg	=	sprintf('%s [%s]',$msg,implode(',',$possibleValues));
					$msg	=	sprintf('%s "%s" was given.',$msg,$value->valueOf());

					throw new ParameterNotIncaseException($msg);

				}

				return $value;

			}

			public function remove($name){

				foreach($this->value as $key=>$value){

					if($key==$name){

						unset($this->value[$key]);
						break;

					}

				}

			}

			public function find($name,$default=NULL,$aliases=NULL){

				$values	=	$this->getValue();

				if(empty($values)){

					$param	=	ParameterType::cast($name,$default);
					return $param;

				}

				foreach($values as $item){

					if(strtolower((string)$item->getName())===(strtolower((string)$name))){

						if(ValidateVar::isTraversable($aliases)){

							return $this->findAliasedValue($name,$default,$aliases);

						}

						return $item;

					}

				}

				if(ValidateVar::isTraversable($default)){

					if(ValidateVar::isTraversable($aliases)){

						return $this->findAliasedValue($name,$default,$aliases);

					}

					return new static($default);

				}


				if(ValidateVar::isTraversable($aliases)){

					return $this->findAliasedValue($name,$default,$aliases);

				}

				return ParameterType::cast($name,$default);

			}

			public function toString(){

				if(!ValidateVar::isTraversable($this->getValue())){

					return "{$this->getValue()}";

				}

				$value	=	!is_null($this->key) ? [$this->key=>$this->getValue()] : $this->getValue();
				return urldecode(http_build_query($value,TRUE));

			}

			public function __toString(){

				return $this->toString();

			}

		}

	}

