<?php

	namespace apf\hw\disk{

		use apf\io\validate\File				as	ValidateFile;
		use apf\type\base\IntNum				as	IntType;
		use apf\type\base\Str					as	StringUtil;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\validate\base\Str		as	ValidateString;
		use apf\type\util\common\Variable	as	VarUtil;
		use apf\type\parser\Parameter			as	ParameterParser;
		use apf\util\convert\meassure\Byte	as	ByteMeassureConverter;

		use apf\iface\convertible\Vector		as	VectorInterface;
		use apf\iface\convertible\Json		as	JSONInterface;

		class Partition implements VectorInterface,JSONInterface{

			private	$name		=	NULL;

			public function __construct($name=NULL){

				$this->setName($name);

			}

			public function setName($name){

				$name	=	VarUtil::printVar($name);
				ValidateString::mustBeNotEmpty($name,'Drive name can not be empty');

				$freeSpace	=	@disk_free_space($name);

				if($freeSpace===FALSE){

					throw new \InvalidArgumentException("Drive \"$name\" doesn't exists");

				}

				$this->name	=	$name;

				return $this;

			}

			public function getName(){

				return $this->name;

			}

			public function getFreeSpace($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$to			=	VarUtil::printVar($parameters->findInsert('in','megabyte')->valueOf());

				$freeSpace	=	\disk_free_space($this->name);

				if($to=='byte'){

					return $freeSpace;

				}

				return ByteMeassureConverter::convert($freeSpace,['from'=>'byte','to'=>$to]);

			}


			public function getTotalSpace($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);		
				$to			=	VarUtil::printVar($parameters->findInsert('in','megabyte')->valueOf());
				$totalSpace	=	\disk_total_space($this->name);

				if($to=='byte'){

					return $totalSpace;

				}

				return ByteMeassureConverter::convert($totalSpace,['from'=>'byte','to'=>$to]);

			}

			public function toArray(){

				return VectorType::cast([
													"name"	=>	$this->name,
													"free"	=>	$this->getFreeSpace(['in'=>'byte']),
													"total"	=>	$this->getTotalSpace(['in'=>'byte'])
				]);

			}

			public function jsonSerialize(){

				return $this->toArray()->valueOf();	

			}

			public function __toString(){

				return sprintf('Partition: %s, Total space: %s MB, Free space: %s MB',$this->name,ceil($this->getTotalSpace(['in'=>'megabyte'])),ceil($this->getFreeSpace(['in'=>'megabyte'])));

			}

		}

	}
