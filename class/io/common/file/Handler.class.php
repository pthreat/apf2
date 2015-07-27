<?php

	namespace apf\io\common\file{

		use apf\type\base\IntNum		as	IntType;
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

			private	$charPosition	=	0;

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

			public function fgetc(){

				return CharType::cast(parent::fgetc());

			}

			public function fread($bytes=1024){

				if(parent::eof()){

					return FALSE;

				}

				$fread	=	parent::fread($bytes);
				return StringType::cast($fread);

			}

			public function fwriteln($contents=NULL){

				return self::fwrite(sprintf('%s%s',$contents,\PHP_EOL));

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

				$seekTo	=	$forwardCursor	?	$pos+strlen($char[0])	:	$pos;
				$this->fseek($seekTo);

				if($forwardCursor){

					$this->charPosition++;

				}

				return CharType::cast($char[0],['strict'=>TRUE]);

			}

			public function getLength(){

				$pos		=	$this->ftell();
				$charPos	=	$this->ftellChar();

				$this->fseek(0);
				$count	=	0;

				while(FALSE !==($char=$this->fgetChar())){

					$count++;

				}

				$this->fseek($pos);
				$this->charPosition	=	$charPos;

				return $count;

			}

			public function fseekEnd(){

				$this->fseek($this->getSize());

			}

			public function fseekChar($charAmount=NULL){

				if($charAmount<0){

					$length		=	$this->getLength();
					$charAmount	=	$length + $charAmount;

				}

				$parameters	=	ParameterParser::parse($charAmount,'amount');
				$charAmount	=	$parameters->find('amount')->toInt()->valueOf();

				for($i=0;$i<$charAmount;$i++){

					$this->fgetChar();

				}

				return 0;

			}

			public function ftellChar(){

				return $this->charPosition;

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
