<?php

	namespace apf\type\base{

		use apf\type\Common;

		use apf\type\collection\base\Char			as	CharCollection;

		use apf\type\util\base\Str						as	StringUtil;

		use apf\type\util\common\Class_				as	ClassUtil;
		use apf\type\util\common\Variable			as	VarUtil;

		use apf\type\parser\Parameter					as	ParameterParser;
		use apf\type\validate\base\Str				as	ValidateString;

		use apf\data\format\JSON;

		use apf\type\exception\Uncastable			as	UncastableException;

		class Str extends StrCommon implements \ArrayAccess,\Iterator{

			private	$strAsArray		=	Array();
			private	$iterateStep	=	1;

			/**
			* Casts a value to a Str type, this includes objects containing the __toString
			* method or plain strings. 
			*
			* @throws \apf\validate\exception\Str If given argument is not a string
			* @return \apf\type\Str in case of success
			*/

			public static function cast($val,$parameters=NULL){

				if(is_a($val,__CLASS__)){

					return $val;

				}

				try{

					$val			=	VarUtil::printVar($val,$parameters);
					$val			=	new static($val,$parameters);

					return $val;

				}catch(\Exception $e){

					throw new UncastableException("Could not convert value to Apollo String type");

				}

			}

			public function keyValuePair($parameters=NULL){

				return StringUtil::keyValuePair($this->value,$parameters);

			}

			public function jsonDecode($parameters=NULL){

				return StringUtil::jsonDecode($this->value,$parameters);

			}

			/**
			*Gets an instance of a string type for the value to be set later
			*@return Str instance
			*/

			public static function instance($parameters=NULL){

				return self::cast("",$parameters);

			}

			public function toBinary($parameters=NULL){

				return StringUtil::toBinary($this->value,$parameters);

			}

			public function beginsWith($string){

				$parameters	=	$this->parseParameters(['match'=>$string],$merge=FALSE);
				return StringUtil::beginsWith($this->value,$parameters);

			}

			/**
			*Cut a string in equal chunks and return a Vector
			*with given chunks.
			*
			*@return Vector A Vector containing the string with the specified chunks
			*/

			public function chunk($chunks){


			}

			public function length($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return IntNum::cast(StringUtil::length($this->value,$parameters));

			}

			public function tokenize($delimiter,$num=0){

				return new static(StringUtil::tokenize($this->value,$delimiter,$num,['cast'=>FALSE]));

			}

			public function explode($pattern=' '){

				if(is_string($pattern)){

					$pattern	=	['pattern'=>$pattern];

				}

				$parameters	=	$this->parseParameters($pattern,$merge=FALSE);

				return StringUtil::explode($this->value,$parameters);

			}

			public function toCamelCase($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::toCamelCase($this->value,$parameters),$parameters);

			}

			public function toSlug($slugChar='-'){

				if(is_string($slugChar)){

					$slugChar	=	['char'=>'-'];

				}

				$parameters	=	$this->parseParameters($slugChar,$merge=FALSE);

				return new static(StringUtil::toSlug($this->value,$parameters),$parameters);

			}

			public function normalizeSpaces($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::normalizeSpaces($this->value,$parameters),$parameters);

			}

			public function deSlug($slugChar='-'){

				if(is_string($slugChar)){

					$slugChar	=	['char'=>'-'];

				}

				$parameters	=	$this->parseParameters($slugChar,$merge=FALSE);

				return new static(StringUtil::deSlug($this->value,$parameters),$parameters);

			}

			public function minify($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::minify($this->value,$parameters),$parameters);

			}

			public function cut($delimiter){

				if(is_string($delimiter)){
	
					$delimiter	=	['delimiter'=>$delimiter];

				}

				$parameters	=	$this->parseParameters($parameters);
				$value		=	StringUtil::cut($this->value,$parameters);

				if($value===FALSE){

					return $this;

				}

				return new static($value,$parameters);

			}

			public function cutLast($delimiter){

				if(is_string($delimiter)){

					$delimiter	=	['delimiter'=>$delimiter];

				}

				$parameters	=	$this->parseParameters($delimiter,$merge=FALSE);

				$value		=	StringUtil::cutLast($this->value,$parameters);

				if($value===FALSE){

					return $this;

				}

				return new static($value,$parameters);

			}

			public function cutFirst($delimiter=NULL){

				if(is_string($delimiter)){

					$delimiter	=	['delimiter'=>$delimiter];

				}

				$parameters	=	$this->parseParameters($delimiter,$merge=FALSE);

				$value		=	StringUtil::cutFirst($this->value,$parameters);

				if($value===FALSE){

					return $this;

				}

				return new static($value,$parameters);

			}

			//This would be pretty much the equivalent of getting a new instance

			public function toString($parameters=NULL){

				return new static($this->value,$parameters);

			}

			/*************************************************************
			*Convertible interface methods
			*/

			/**
			*Turn a string into a Char collection
			*
			*@method toArray
			*@return apf\collection\Char A character collection
			*/

			public function toArray($parameters=NULL){

				$parameters			=	$this->parseParameters($parameters,$merge=FALSE);
				return StringUtil::toArray($this->value,$parameters);

				$charCollection	=	new CharCollection(Array(),$parameters);
				$array				=	$this->strAsArray();

				foreach($array as $k=>$v){

					$charCollection->add(Char::cast($v));

				}

				return $charCollection;

			}

			public function toBoolean($parameters=NULL){

				return BooleanType::cast(StringUtil::strim($this->value,$parameters),$parameters);

			}

			public function toInt($parameters=NULL){

				return IntNum::cast($this->length(),$parameters);

			}

			public function toReal($parameters=NULL){

				return RealNum::cast($this->value,$parameters);

			}

			public function toJSON(){
			}

			public function toChar(){

				return CharType::cast($this->value,$this->parameters);

			}

			/*End of convertible interface methods
			 *************************************************************/

			private function strAsArray(){

				if(!empty($this->strAsArray)){

					return $this->strAsArray;

				}

				$this->strAsArray	=	Vector::cast($this->value,$this->parameters);
				$this->strAsArray->autoCast(FALSE);

				return $this->strAsArray;

			}

			/*******************************************************
			*Iterator interface methods
			*/

			public function key(){
	
				return $this->strAsArray->key();

			}

			public function rewind(){

				$this->strAsArray	=	Array();

			}

			public function current(){

				return Char::cast($this->strAsArray->current(),$this->parameters);

			}

			public function next(){

				$char	=	$this->strAsArray()->next();

				if($char===FALSE){
					return FALSE;
				}

				return Char::cast($char,$this->parameters);

			}

			public function valid(){

				$this->strAsArray();

				return $this->strAsArray->valid();

			}

			/*End of Iterator interface methods
			********************************************************/

			/*******************************************
			*Array access interface methods
			*/

			public function offsetSet($offset,$value){
			
				$this->strAsArray();
				$this->strAsArray[$offset]	=	$value;
				$this->value					=	$this->strAsArray->implode()->valueOf();
				$this->strAsArray				=	NULL;

			}

			public function offsetExists($offset){

				return $this->strAsArray()->hasKey($offset);

			}

			public function offsetGet($offset){

				return Char::cast($this->strAsArray()[$offset]);

			}

			public function offsetUnset($offset){

				unset($this->value[$offset]);

			}

			/*Array access interface methods
			*******************************************/

			public function substr($start,$length=NULL){

				return new static(StringUtil::substr($this->value,['start'=>$start,'end'=>$length]));

			}

			public function reverse(){

				return Vector::cast($this->value)->reverse()->toString();

			}


		}

	}

