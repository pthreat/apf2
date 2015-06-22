<?php

	namespace apf\io\common\file{

		use apf\type\base\Str			as	StringType;
		use apf\type\base\Char			as	CharType;
		use apf\type\parser\Parameter	as	ParameterParser;
		use apf\iface\Convertible		as	ConvertibleInterface;
		use apf\type\util\base\Str		as	StringUtil;
		use apf\io\File;

		class Handler extends \SplFileObject implements ConvertibleInterface{

			//$this->parameters is defined in trait \apf\type\parse\Parameter
			//parseParameters aswell.

			use \apf\traits\type\parser\Parameter;

			public function current(){
	
				return StringType::cast(parent::current(),$this->parameters);

			}

			public function fgets(){

				if(parent::eof()){

					return FALSE;

				}

				$fgets	=	parent::fgets();

				return StringType::cast($fgets);

			}

			public function fread($bytes=1024){

				if(parent::eof()){

					return FALSE;

				}

				$fread	=	parent::fread($bytes);
				return StringType::cast($fread);

			}

			public function fgetc(){

				$char	=	parent::fread(2);

				var_dump($char);
				die();

				if($char===FALSE){

					return FALSE;

				}

				return CharType::cast($char,['strict'=>TRUE]);

			}

			public function seekToChar($seekChar){

				if($this->eof()){

					return FALSE;

				}

				while(FALSE !== ($char=$this->fgetc())){

					if($seekChar===$char->valueOf()){

						return TRUE;

					}

				}

				return FALSE;

			}

			public function rewindCursor(){

				return parent::fseek(0);

			}	

			public function fwrite($str,$length=NULL){

				$str	=	StringUtil::toUTF8($str);

				if(!is_null($length)){

					return parent::fwrite($str,$length);

				}

				return parent::fwrite($str);

			}

			public function fputc($char){

				$char	=	StringUtil::toUTF8($str);
				return parent::fwrite($char);

			}

			public function toChar(){
			}

			public function toString(){
			}

			public function toJSON(){
			}

			public function toInt(){
			}

			public function toReal(){
			}

			public function toArray(){
			}

			public function toBoolean(){
			}

			public function jsonSerialize(){
			}

		}

	}
