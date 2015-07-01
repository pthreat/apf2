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

			/**
			*fgetChar is different from fgetc due to the fact
			*that fgetChar reads characters, not bytes as fgetc does.
			*This allows the end user to safely go character by character,
			*it doesn't matters if the character is a multibyte character.
			*/

			public function fgetChar($forwardCursor=TRUE){

				$pos			=	$this->ftell();
				$char			=	parent::fread(32);
				$char			=	preg_split('//u',$char,-1,\PREG_SPLIT_NO_EMPTY);

				if(!sizeof($char)){

					return FALSE;

				}

				$size		=	sizeof($char);

				$seekTo	=	$forwardCursor	?	$pos+strlen($char[0])	:	$pos;
				$this->fseek($seekTo);

				return CharType::cast($char[0],['strict'=>TRUE]);

			}

			public function seekToChar($seekChar){

				if($this->eof()){

					return FALSE;

				}

				while(FALSE !== ($char=$this->fgetChar())){

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
