<?php

	namespace apf\hw{

		use apf\type\base\IntNum				as	IntType;
		use apf\type\base\Str					as	StringUtil;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\parser\Parameter			as	ParameterParser;
		use apf\util\convert\meassure\Byte	as	ByteMeassureConverter;

		//Every value will be normalized to BYTES
		//on getters you can specify what type of value conversion you'd like
		//Megabyte, Gigabyte and so on

		class Memory implements \jsonSerializable{

			private	$ramTotal	=	NULL;
			private	$ramFree		=	NULL;
			private	$swapTotal	=	NULL;
			private	$swapFree	=	NULL;
			private	$phpTotal	=	NULL;
			private	$info			=	NULL;

			public function __construct($info=NULL){

				if(!is_null($info)){

					$this->setInfo($info);

				}

			}

			public function setInfo(Array $info){

				$this->info	=	$info;
				return $this;

			}

			public function getInfo(){

				return $this->info;

			}

			public function getPHPTotal($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$memory		=	ini_get('memory_limit');

				if($memory==-1){

					return $this->getRAMTotal($parameters);

				}

				$parameters->replace('from','megabyte');
				$parameters->findInsert('in','byte');

				if(VarUtil::printVar($parameters->find('in')->valueOf())=='megabyte'){

					return (int)$memory;

				}

				return ByteMeassureConverter::convert($memory,$parameters);

			}

			public function getPHPUsage($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$realUsage	=	(boolean)$parameters->findInsert('real',FALSE);
				$memory		=	memory_get_usage($realUsage);

				$parameters->findInsert('in','byte');

				if(VarUtil::printVar($parameters->find('in')->valueOf())=='byte'){

					return $memory;

				}

				return ByteMeassureConverter::convert($memory,$parameters);

			}

			public function setRAMTotal($amount,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters);

				$parameters->findInsert('from','kilobyte');
				$parameters->replace('to','byte');
				$this->ramTotal	=	ByteMeassureConverter::convert((double)$amount,$parameters);

				return $this;

			}

			public function getRAMTotal($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->replace('from','byte');
				$parameters->findInsert('in','megabyte');

				return ByteMeassureConverter::convert($this->ramTotal,$parameters);

			}

			public function setRAMFree($amount,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters);

				$parameters->findInsert('from','kilobyte');
				$parameters->replace('to','byte');

				$this->ramFree	=	ByteMeassureConverter::convert((double)$amount,$parameters);

				return $this;

			}

			public function getRAMFree($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->replace('from','byte');
				$parameters->findInsert('to','megabyte');

				return ByteMeassureConverter::convert($this->ramFree,$parameters);

			}

			public function setSwapTotal($amount,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters);

				$parameters->findInsert('from','kilobyte');
				$parameters->replace('to','byte');

				$this->swapTotal	=	ByteMeassureConverter::convert((double)$amount,$parameters);

				return $this;

			}

			public function getSwapTotal($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->replace('from','byte');
				$parameters->findInsert('to','megabyte');

				return ByteMeassureConverter::convert($this->swapTotal,$parameters);

			}

			public function setSwapFree($amount,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters);

				$parameters->findInsert('from','kilobyte');
				$parameters->replace('to','byte');

				$this->swapFree	=	ByteMeassureConverter::convert((double)$amount,$parameters);

				return $this;

			}

			public function getSwapFree($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->replace('from','byte');
				$parameters->findInsert('to','megabyte');

				return ByteMeassureConverter::convert($this->swapFree,$parameters);

			}

			public function jsonSerialize(){

				return $this->info;

			}

			public function __toString(){

				return sprintf('PHP memory: %s MB, RAM Total: %s MB, RAM Free: %s MB, Swap Total: %s MB, Swap Free: %s MB',ceil($this->getPHPTotal()),ceil($this->getRAMTotal()),ceil($this->getRAMFree()),ceil($this->getSwapTotal()),ceil($this->getSwapFree()));

			}

		}

	}
