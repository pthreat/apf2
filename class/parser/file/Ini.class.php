<?php

	namespace apf\parser\file{

		use apf\io\validate\File					as	ValidateFile;
		use apf\type\base\Vector					as	VectorType;
		use apf\type\util\common\Variable		as	VarUtil;
		use apf\type\util\base\Vector				as	VectorUtil;
		use apf\type\parser\Parameter				as	ParameterParser;
		use apf\parser\exception\CouldNotParse	as	CouldNotParseException;

		class Ini{

			private	$file	=	NULL;

			public function __construct($file=NULL){

				if(!is_null($file)){

					$this->setFile($file);

				}

			}

			public function setFile($file,$parameters=NULL){

				ValidateFile::mustBeReadable($file,$parameters);
				$this->file	=	VarUtil::printVar($file,$parameters);
				return $this;

			}

			public function parse($parameters=NULL){

				if(is_null($this->file)){

					throw new \InvalidArgumentException("Ini file was not set");

				}

				$parameters	=	ParameterParser::parse($parameters);
				$as			=	VarUtil::printVar($parameters->selectCase('as',['array','stdclass'],'stdclass'));
	
				$parse		=	parse_ini_file($this->file,TRUE);

				if($as=='array'){

					return $parse;	

				}

				return VectorUtil::toStdClass($as,['into'=>$this]);

			}

			public function __set($var,$value){

				$this->$var	=	$value;

			}

			public function __get($var){

				throw new \InvalidArgumentException("Invalid ini parameter \"$var\"");

			}

		}

	}
