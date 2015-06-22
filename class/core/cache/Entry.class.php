<?php

	namespace apf\core\cache{

		use apf\type\base\IntNum				as	IntType;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\validate\base\Str		as	ValidateString;
		use apf\type\parser\Parameter			as	ParameterParser;

		abstract class Entry{

			protected	$ttl		=	NULL;
			protected	$name		=	NULL;
			protected	$value	=	NULL;
			protected	$size		=	NULL;
			protected	$source	=	NULL;

			public function __construct($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$this->setSource($parameters->demand('source')->valueOf());
				$this->setName($parameters->demand('name')->valueOf());
				$this->setTTL($parameters->demand('ttl')->valueOf());
				$this->setValue($parameters->demand('value')->valueOf());

			}

			public function setTTL($ttl){

				$this->ttl	=	IntType::cast($ttl)->valueOf();
				return $this;

			}

			public function getTTL(){

				return $this->ttl;

			}

			public function setName($name=NULL){

				$name	=	VarUtil::printVar($name);
				ValidateString::mustBeNotEmpty($name,['msg'=>'Cache name can not be empty']);
				$this->name	=	$name;
				return $this;

			}

			public function getName(){

				return $this->name;

			}

			public function setSource($source=NULL){

				$source	=	VarUtil::printVar($source);
				ValidateString::mustBeNotEmpty($source,['msg'=>'Cache source can not be empty']);
				$this->source	=	$source;
				return $this;

			}

			public function getSource(){

				return $this->source;

			}

			public function setValue($value){

				$this->value	=	serialize($value);
				return $this;

			}

			public function getValue(){

				return $this->value;

			}

			public function setSize($size=NULL){

				$this->size	=	IntType::cast($size)->valueOf();
				return $this;

			}

			public function getSize(){

				return $this->size;

			}

			abstract public function delete();

			public function __toString(){

				return sprintf('Name: %s, TTL: %s, Size: %s',$this->name,$this->ttl,$this->size);

			}

		}

	}
