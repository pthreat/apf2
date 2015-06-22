<?php

	namespace apf\hw{

		use apf\type\base\IntNum				as	IntType;
		use apf\type\base\Str					as	StringType;
		use apf\type\validate\base\IntNum	as ValidateInt;
		use apf\type\validate\base\Str		as ValidateString;
		use apf\type\util\base\Str				as StringUtil;

		class CPU implements \jsonSerializable{

			private	$info			=	NULL;
			private	$model		=	NULL;
			private	$cacheSize	=	NULL;
			private	$flags		=	Array();
			private	$arch			=	NULL;
			private	$vendor		=	NULL;
			private	$number		=	0;
			private	$hasFPU		=	NULL;
			private	$cores		=	0;

			public function __construct($info){

				$this->info	=	$info;

			}

			public function setFlags($flags){

				return $this;

			}

			public function setVendor($vendor){

				$msg				=	"CPU vendor can't be empty";
				$this->vendor	=	ValidateString::mustBeNotEmpty($vendor,$trim=TRUE,$msg);

				return $this;

			}

			public function setAmountOfCores($cores){

				$this->cores	=	IntType::cast($cores)->valueOf();
				return $this;

			}

			public function getAmountOfCores(){

				return IntType::cast($this->cores);

			}

			public function getVendor(){

				return $this->vendor;

			}

			public function setFPU($boolean){

				$this->hasFPU	=	(boolean)$boolean;
				return $this;

			}

			public function getFPU(){

				return $this->hasFPU;

			}

			public function setNumber($number){

				$this->number	=	ValidateInt::mustBePositive($number);
				return $this;
				
			}

			public function getNumber(){

				return $this->number;

			}

			public function addFlag($flag){

				ValidateString::mustBeNotEmpty($model,$trim=TRUE,"CPU flag can't be empty");

				if(!in_array($flag,$this->flags)){

					$this->flags[]	=	$flag;

				}

				return $this;

			}

			public function setModel($model){

				ValidateString::mustBeNotEmpty($model,$trim=TRUE,"CPU Model can't be empty");
				$this->model	=	$model;

				return $this;

			}

			public function getModel(){

				return $this->info->model;

			}

			public function setMhz($mhz){

				$this->mhz	=	$mhz;
				return $this;

			}

			public function getMhz(){

				return $this->mhz;

			}

			public function setCacheSize($cacheSize){

				$this->cacheSize	=	$cacheSize;
				return $this;

			}

			public function getCacheSize(){

				return $this->cacheSize;

			}

			public function jsonSerialize(){

				return[
							'number'		=>	$this->number,
							'model'		=>	$this->model,
							'cacheSize'	=>	$this->cacheSize,
							'flags'		=>	$this->flags,
							'arch'		=>	$this->arch,
							'vendor'		=>	$this->vendor,
							'hasFPU'		=>	$this->hasFPU,
							'cores'		=>	$this->cores,
							'info'		=>	$this->info
				];

			}

			public function __toString(){

				return sprintf('CPU %d: %s, Cache size: %s, Cores: %s',$this->number,$this->model,$this->cacheSize,$this->cores);

			}

			public function getInfo(){

				return $this->info;

			}

		}

	}
