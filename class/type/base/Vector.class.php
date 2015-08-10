<?php

	namespace apf\type\base{

		use apf\type\collection\base\Vector						as	VectorCollection;
		use apf\type\validate\common\Obj							as	ValidateObj;
		use apf\type\validate\common\Variable					as	ValidateVar;

		use apf\type\parser\Parameter								as	ParameterParser;

		use apf\type\util\base\Vector								as	VectorUtil;
		use apf\type\util\base\Str									as StringUtil;

		use apf\type\util\common\Class_							as	ClassUtil;
		use apf\type\util\common\Variable						as VarUtil;

		use apf\util\convert\meassure\Byte						as	ByteMeassureConverter;

		use apf\iface\type\Convertible							as	ConvertibleInterface;

		use apf\type\exception\base\vector\UndefinedIndex	as	UndefinedIndexException;
		use apf\type\exception\base\vector\ValueNotFound	as	ValueNotFoundException;
		use apf\type\exception\base\vector\Locked				as	LockedException;
		use apf\type\exception\common\Uncastable				as	UncastableException;
		use apf\core\exception\OutOfMemory						as	OutOfMemoryException;

		class Vector extends VectorCommon{

			protected	$value			=	Array();

			protected function __construct($val=Array(),$parameters=NULL){

				if(!is_array($val)){

					$val	=	Array();

				}

				parent::__construct($val,$parameters);

				$parameters				=	$this->parameters;
				$fixToFirstElement	=	$parameters->find('autoFix',FALSE)->toBoolean()->valueOf();
				$allowedTypes			=	$parameters->find('allowedTypes')->valueOf();

				if(!is_null($allowedTypes)){

					if(!ValidateVar::isTraversable($allowedTypes)){

						$allowedTypes	=	[$allowedTypes];

					}

					foreach($allowedTypes as $allowedType){

						$this->addAllowedType($allowedType);

					}

				}

				if($fixToFirstElement){

					$type	=	gettype($val);

					$type=='object' ? $this->addAllowedType(['value'=>get_class($val),'type'=>'class']) :
											$this->addAllowedType(['value'=>$type,'type'=>'primitive']);

				}

				$this->autoCast	=	(boolean)$parameters->find('autoCast',FALSE)->valueOf();

			}

			public static function cast($val,$parameters=NULL){

				if(is_a($val,__CLASS__)){

					return $val;

				}

				$type	=	strtolower(gettype($val));

				switch($type){

					case 'array':

						return new static($val,$parameters);

					break;

					case 'string':

						return StringUtil::toArray($val,$parameters);

					break;

					case 'integer':

						return IntUtil::toArray($val,$parameters);

					break;

					case 'null':

						return new static(Array(),$parameters);

					break;

					case 'object':

						if($val instanceof \ArrayObject){

							return new static($val,$parameters);

						}

						if($val instanceof ConvertibleInterface){

							return $val->toArray();

						}

						if(ValidateObj::isTraversable($val)){

							return new static(VarUtil::traverse($val),$parameters);

						}

						if($val instanceof \stdClass){

							return new static((Array)$val);

						}

						if(ClassUtil::hasMethod('toArray')){

							return new static(VarUtil::traverse($val->toArray()));

						}

					break;

					default:

						return new static([$val],$parameters);

					break;

				}

			}

			public function filterInclude($regex){

				foreach($this as $k=>$v){

					if(StringUtil::match($v,["match"=>$regex])){
						unset($this[$k]);
					}

				}

				return $this;

			}

			public function filterExclude($regex){

				foreach($this as $k=>$v){

					if(!StringUtil::match($v,["match"=>$regex])){
						unset($this[$k]);
					}

				}

				return $this;

			}

			public function getMemorySize(){

				return VarUtil::getSize($this);

			}

			public static function instance($parameters=NULL){

				return new static(Array(),$parameters);

			}

			public function merge(){

				$args	=	func_get_args();

				foreach($args as $arg){

					$this->add($arg,$this->parameters);

				}

				return $this;

			}

			public function diff(){

				$args		=	func_get_args();

				foreach($args as &$arg){

					$arg	=	self::cast($arg);	

				}


			}

			public static function getInstanceFromKeys(Array $value){

				return new static(array_keys($value));

			}

			public function chunk($size){

				$size			=	IntNum::cast($size)->valueOf();

				if($size<=0){

					throw new \InvalidArgumentException("Invalid chunk size $size");

				}

				$collection	=	new VectorCollection();
				$chunks		=	array_chunk($this->value,$size);

				foreach($chunks as $val){

					$collection->add(new static($val));

				}

				return $collection;

			}

			//Alias of self::offsetExists

			public function hasKey($key){

				return array_key_exists($key,$this->value);

			}

			public function inArray($val){

				return VectorUtil::inArray($val,$this);

			}

			public function hasKeys($keys){

				return VectorUtil::hasKeys($keys);

			}

			public function hasExactKeys($keys){

				$keys	=	self::cast($keys);

				return $this->hasKeys($keys)->size() == $keys->size();

			}

			public function getValueOfFirstMatchedKey($keys){

				$keys	=	self::cast($keys);

				foreach($keys as $k=>$val){

					if($this->hasKey($k)){

						return $val;

					}

				}

				return FALSE;

			}

			private function checkMemory(&$item,$parameters=NULL){

				$parameters	=	$this->parseParameters($parameters);

				$memory		=	$parameters->find('maxMemory')->valueOf();

				if($memory){

					$bytes		=	ByteMeassureConverter::convert($memory['amount'],['from'=>$memory['meassure'],'to'=>'byte']);

					$curSize		=	memory_get_usage();

					if($curSize>$bytes){
	
						$msg	=	VarUtil::printVar($item);
						throw new OutOfMemoryException("Out of memory: Tried to add {$msg}");

					}

				}

			}

			/**
			*Method			:	add
			*Description	:	Adds an item to this vector
			*@param mixed $item The item to be added
			*@param mixed $key Optional. The key of the element to be added to this vector
			*@param Array $options Casting options
			*/

			public function add($item,$parameters=NULL){

				$parameters	=	$this->parseParameters($parameters);

				$this->checkMemory($item,$parameters);

				//We could use memory calculations through the Platform component 
				//to know if adding the item would "overflow" the allowed PHP memory

				/**
				*Something like: 
				*		Platform::hasSufficientMemory($item,['throw'=>TRUE]);
				*		//This would throw an InsufficientMemory Exception
				*
				*/

				$key	=	$parameters->find('key',FALSE)->valueOf();

				$key===FALSE	?	($this[]	=	$item)	:	($this[VarUtil::printVar($key,$parameters)]	=	$item);

				return $this;

			}

			public function addByReference(&$item,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				//We could use memory calculations through the Platform component 
				//to know if adding the item would "overflow" the allowed PHP memory

				/**
				*Something like: 
				*		Platform::hasSufficientMemory($item,['throw'=>TRUE]);
				*		//This would throw an InsufficientMemory Exception
				*
				*/

				$key	=	$parameters->find('key',FALSE)->valueOf();

				$key===FALSE	?	($this[]	=	$item)	:	($this[VarUtil::printVar($key,$parameters)]	=	$item);

				return $this;

			}

			/**
			*Method			:	push
			*Description	:	Alias of add
			*@see self::add
			*/

			public function push($item,$options){

				return $this->add($item,$options);

			}

			/**
			*Method			:	size
			*Description	:	Returns the amount of items in this Vector
			*@return IntNum The amount of elements inside this vector expressed as an IntNum
			*/

			public function size(){

				return IntNum::cast(count($this->value));

			}

			/**
			*Method			:	walk
			*Description	:	Walks each (first sibling) element of a vector
			*
			*Example			:
			*
			*<code>
			*	$array = Array(1,2,3);
			*
			*	//This would walk through all values and add 1 to each one of the numbers
			*	//contained in $array
			*
			*	//Mind you, each $val is an Apollo type, that is, if you haven't set
			*	//use cast to FALSE.
			*
			*	$vector = VectorType::cast($array)->rwalk(function(&$val,$key){
			*		return $val+1;
			*	});
			*
			*</code>
			*
			*@see self::autoCast
			*
			*/

			public function walk(Callable $fn,$userData=NULL){

				return VectorUtil::walk($this->value,$fn,$userData);

			}

			/**
			*Method			:	rwalk
			*Description	:	Walks a Vector completely (all of its childs)
			*And applies an anonymous function to each one of the values.
			*The behaviour of the values given to your function affected 
			*by self::autoCast.
			*
			*Example			:
			*
			*<code>
			*	$array = Array(
			*						"customers"=>Array(
			*													Array(
			*															"name"=>"john"
			*													)
			*						),
			*						"salesmen" => Array(
			*													Array(
			*															"name"=>"jeremy"
			*													)
			*						)
			*	);
			*
			*	//This would walk through all array values and capitalize
			*	//each name.
			*
			*	//Mind you, each $val is an Apollo type, that is, if you haven't set
			*	//use cast to FALSE.
			*
			*	$vector = VectorType::cast($array)->rwalk(function(&$val,$key){
			*
			*		if($key=='name'){
			*
			*			return ucwords($val);
			*
			*		}
			*
			*	});
			*</code>
			*
			*@see self::autoCast
			*
			*/

			public function rwalk(Callable $fn,$userData=NULL){

				return VectorUtil::rWalk($this->value,$fn,$userData);

			}

			public function fill($value,$start,$times){

				$start	=	IntNum::cast($start);
				$times	=	IntNum::cast($times);

				$this->value	=	array_fill($start->valueOf(),$times->valueOf(),$this->value);

				return $this;

			}

			public function onGetCastTo($parameters=NULL){
			}

			public function onSetCastTo($parameters=NULL){
			}

			public function implode($parameters=NULL){

				return VectorUtil::implode($this->value,$parameters);

			}

			/*******************************************
			*Type conversion methods
			*/

			public function toInt($parameters=NULL){

				return IntNum::cast(count($this->value),$parameters);

			}

			public function toBoolean($parameters=NULL){

				return Boolean::cast(count($this->value),$parameters);

			}

			public function toObject($parameters=NULL){

				return $this;

			}

			public function toString($parameters=NULL){

				return Str::cast(json_encode($this),$parameters);

			}

			/**
			*Method			:	toStdClass
			*Description	:	Converts an array into an stdClass object (recursively)
			*@return stdClass stdClass representation of the array
			*/

			public function toStdClass(){

				return VectorUtil::toStdClass($this->value,$this->parameters);

			}

			/*End of type conversion methods
			 *******************************************/

			/*******************************************
			*Array access interface methods
			*/

			public function offsetSet($offset,$value){

				if($this->locked){

					$msg	=	"This vector is locked, elements can not be added";
					$msg	=	sprintf('%s or modified, please see self::unlock',$msg);
					throw new LockedException($msg);

				}

				if(sizeof($this->allowedTypes)){

					$this->validateItem($value);

				}

				$offset	=	is_null($offset)	?	count($this->value)	:	VarUtil::printVar($offset);

				$this->checkMemory($value);

				$this->value[$offset]	=	$this->autoCast	?	parent::castAny($value)	:	$value;
				$this->timesSet++;

			}

			public function offsetExists($offset){

				return array_key_exists($offset,$this->value);

			}

			public function offsetGet($offset){

				if(!array_key_exists($offset,$this->value)){

					throw new UndefinedIndexException("No such index $offset");

				}

				return $this->autoCast ? parent::castAny($this->value[$offset]) : $this->value[$offset];

			}

			public function offsetUnset($offset){

				unset($this->value[$offset]);

			}

			/*Array access interface methods
			*******************************************/

			public function find($value){

				return VectorUtil::find($this->value,$value,$this->parameters);

			}

			public function indexOf($value){

				return VectorUtil::indexOf($this->value,$value);

			}

			public function indexesOf($value){

				return VectorUtil::indexesOf($this->value,$value);

			}

			public function lastIndexOf($value){

				return VectorUtil::lastIndexOf($this->value,$value);

			}

			public function sort($parameters=NULL){

				sort($this->value,$this->parseSortingOrder($parameters));

				return $this;

			}

			public function ksort($parameters=NULL){

				ksort($this->value,$this->parseSortingOrder($parameters));
				return $this;

			}

			public function asort($parameters=NULL){

				asort($this->value,$this->parseSortingOrder($parameters));
				return $this;

			}

			public function rsort($parameters=NULL){

				rsort($this->value,$this->parseSortingOrder($parameters));
				return $this;

			}

			public function natSort(){

				natsort($this->value);
				return $this;

			}

			public function shuffle(){

				shuffle($this->value);
				return $this;

			}

			public function shift(){

				return $this->autoCast	?	parent::castAny(array_shift($this->value))	:
													array_shift($this->value);

			}

			public function flip(){

				$vector	=	self::instance($this->parameters);

				foreach($this->value as $key=>$value){

					$vector[VarUtil::printVar($value)]	=	$key;

				}

				$this->value	=	$vector->valueOf();

				return $this;

			}

			public function pop(){

				if($this->locked){

					throw new \RuntimeException("Can not pop value on this Vector since it's locked");

				}

				return $this->autoCast	?	parent::castAny(array_pop($this->value))	:	
													array_pop($this->value);

			}

			public function reverse(){

				$this->value	=	array_reverse($this->value);
				return $this;

			}

			public function toChar(){
			}

			public function toJSON(){
			}

		}

	}

