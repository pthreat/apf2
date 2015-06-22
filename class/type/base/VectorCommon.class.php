<?php

	namespace apf\type\base{

		use apf\type\Common	as	Type;

		use apf\iface\type\base\Vector	as	VectorInterface;
		use apf\type\Common;

		use apf\type\collection\base\Vector						as	VectorCollection;
		use apf\type\validate\common\Obj							as	ValidateObj;
		use apf\type\validate\common\Variable					as	ValidateVar;

		use apf\type\parser\Parameter								as	ParameterParser;
		use apf\type\util\base\Str									as StringUtil;

		use apf\type\util\common\Class_							as	ClassUtil;
		use apf\type\util\common\Variable						as VarUtil;

		use apf\iface\type\Convertible							as	ConvertibleInterface;

		use apf\type\exception\base\vector\UndefinedIndex	as	UndefinedIndexException;
		use apf\type\exception\base\vector\ValueNotFound	as	ValueNotFoundException;
		use apf\type\exception\base\vector\Locked				as	LockedException;
		use apf\type\exception\common\Uncastable				as	UncastableException;

		abstract class VectorCommon extends Type implements VectorInterface{

			use \apf\traits\Traversable;

			protected	$locked			=	FALSE;
			protected	$unlockable		=	NULL;
			protected	$allowedTypes	=	Array();
			protected	$timesSet		=	0;

			public function setUnlockable($boolean=FALSE){

				if(!is_null($this->unlockable)){

					$msg	=	"Can not set a lock on a vector after it has been set unlockable";
					throw new \RuntimeException($msg);

				}

				$boolean	=	ParameterParser::parse($boolean,FALSE)->toBoolean()->valueOf();

			}


			/**
			*Specifies elements must not be added to the vector (i.e locking it)
			*If you add elements to a locked Vector an exception would be thrown.
			*@method Lock
			*@return Vector
			*/

			public function lock(){

				$this->lock	=	TRUE;
				return $this;

			}


			/**
			*Unlocks the vector in case it was locked (i.e allows elements to be added)
			*Beware, if you have set the vector as setUnlockable(FALSE) an exception will be
			*thrown.
			*@method Unlock
			*@return Vector
			*@see self::setUnlockable
			*@throws \RuntimeException in case the vector has been set unlockable FALSE
			*/

			public function unlock(){

				if(!$this->unlockable){

					throw new \RuntimeException("This vector instance can not be unlocked");

				}

				$this->lock	=	FALSE;
				return $this;

			}


			protected function validateItem($item){

				foreach($this->allowedTypes as $type){

					$itemType	=	gettype($item);

					if($type['type']=='primitive'&&$itemType==$type['value']){

						return TRUE;	

					}

					if($itemType=='object'&&is_a($item,$type['value'])){

						return TRUE;

					}

				}

				$msg  =  Array();

				foreach($this->allowedTypes as $type){

					$msg[]   =  $type['type']=='class'  ?  "a \"$type[value]\" class" : 
									"a \"$type[value]\" PHP type";

				}

				$type	=	gettype($item);

				if($type=="object"){

					$type	=	get_class($item);

				}

				$msg	=	implode(' or ',$msg);
				$msg	=	sprintf('This instance only accepts %s, "%s" was given.',$msg,$type);

				throw new \InvalidArgumentException($msg);

			}

			public function getAllowedTypes(){

				return $this->allowedTypes;

			}


			/**
			*Fixes a Vector instance to only accept parameters of certain types
			*
			*You can fix a vector to accept only a primitive of a certain type (ie:string)
			*You can fix a vector to accept only a primitive of certain types (ie:int,string)
			*You can fix a vector to accept only a certain class (ie: stdClass)
			*You can fix a vector to accept only a certain class and a certain primitive (ie:stdClass,int)
			*
			*@param mixed $value Class name or Primitive type
			*@throws \InvalidArgumentException with code 1 in case the specified primitive type is invalid
			*@throws \InvalidArgumentException with code 2 in case the specified class does not exists.
			*/

			public function addAllowedType($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$value		=	$parameters->demand('value')->toString()->valueOf();;
				$type			=	$parameters->selectCase('type',['primitive','class'])
				->toString()
				->valueOf();

				if($type=='primitive'){

					$primitiveTypes	=	[
													'string','array','callback','integer','boolean',
													'double','float','resource','NULL'
					];

					if(!in_array($value,$primitiveTypes)){

						$msg	=	"$value is an invalid primitive type ";
						$msg	=	sprintf('%s. Allowed types are: %s',$msg,implode(' or ',$primitiveTypes));

						throw new \InvalidArgumentException($msg);

					}

				}else{

					if(!class_exists($value)){

						throw new \InvalidArgumentException("Class \"$value\" doesn't exists");

					}
					
				}

				$allowedType	=	['type'=>$type,'value'=>$value];

				if(!in_array($allowedType,$this->allowedTypes)){
					
					$this->allowedTypes[]	=	$allowedType;

				}

				return $this;

			}

			protected function parseSortingOrder($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$sortTypes	=	['regular','numeric','string','locale_string','natural','flag_case'];
				$parameters->findInsert('type','regular');

				$sortType	=	$parameters->selectCase('type',$sortTypes)->valueOf();

				return constant(strtoupper("SORT_$sortType"));

			}

			public function __set($name,$value){

				$this->offsetSet($name,$value);

			}

			public function __get($name){

				return $this->offsetGet($name);

			}

			public function __isset($offset){

				return $this->offsetExists($offset);

			}

			public function __unset($offset){

				$this->offsetUnset($offset);

			}

			public function __toString(){

				$copy	=	[];

				foreach($this as $val){

					$copy[]	=	VarUtil::printVar($val);

				}

				return json_encode($copy);

			}

			//Reason why we don't use array_pad, simple, we have value type validations 
			//thus we use a simple for loop and use offsetSet which contains the necessary
			//validations.

			public function pad($value,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters,'length');
				$length			=	(int)$parameters->demand('length')->valueOf();

				for($i=0;$i<$length;$i++){

					$this->offsetSet(NULL,$value);

				}

				return $this;

			}

		}

	}
