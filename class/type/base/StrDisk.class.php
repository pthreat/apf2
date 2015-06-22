<?php

	namespace apf\type\base{

		use apf\io\File;
		use apf\type\base\IntNum								as	IntType;
		use apf\type\base\Char									as	CharType;
		use apf\type\parser\Parameter							as	ParameterParser;
		use apf\type\util\base\Str								as	StringUtil;
		use apf\type\util\common\Variable					as	VarUtil;
		use apf\type\Common										as	Type;

		use apf\type\exception\base\str\UndefinedOffset	as	UndefinedOffsetException;

		class StrDisk extends Type implements \ArrayAccess,\Iterator{

			private	$file	=	NULL;

			public function __construct($string,$parameters=NULL){

				$this->parseParameters($parameters);
	
				if($string instanceof File){

					$this->file	=	$string;
					return;

				}

				$this->file	=	new File(['tmp'=>TRUE]);
				$handler		=	$this->file->getHandler(['mode'=>'w']);
				$handler->fwrite($string);
				$handler->fflush();
				clearstatcache(TRUE,$this->file);

			}

			public static function cast($value,$parameters=NULL){

				return new static($value,$parameters);

			}

			public static function instance($parameters=NULL){

				return new static("");

			}

			public function offsetGet($offset){

				$handler	=	$this->file->getHandler(['mode'=>'c']);
				$handler->fseek($offset);
				return $handler->fgetc();

			}

			public function offsetSet($offset,$value){

				if(is_null($offset)){

					$offset	=	$this->strlen()+1;	

				}

				$handler	=	$this->file->getHandler(['mode'=>'c','reopen'=>TRUE]);
				$handler->fseek($offset);
				$handler->fflush();
				$handler->fwrite(CharType::cast($value)->valueOf());

			}

			public function current(){

				return $this->file->getHandler(['mode'=>'r'])->fread();

			}

			public function next(){

				return $this->current();

			}

			public function rewind(){

				$this->file->getHandler(['mode'=>'r'])->fseek(0);

			}

			public function valid(){

				return !$this->file->getHandler(['mode'=>'r'])->eof();

			}

			public function key(){

				return $this->file->getHandler(['mode'=>'r'])->ftell();

			}

			public function toChar(){
			}

			public function offsetUnset($offset){

				$file			=	new File(['tmp'=>TRUE]);
				$nhandler	=	$file->getHandler();
				$handler		=	$this->file->getHandler();

				while(FALSE!==($char=$handler->fgetc())){

					if($handler->ftell()==$offset){

						continue;

					}

					$handler->fwrite($char);

				}

				$this->file	=	NULL;
				$this->file	=	$file;

			}

			public function offsetExists($offset){

				$handler	=	$this->file->getHandler(['mode'=>'r']);

				if($offset>$handler->getSize()){

					throw new UndefinedOffsetException("Undefined offset \"$offset\"");

				}

				return TRUE;

			}

			public function strlen(){

				$count	=	0;

				$handler	=	$this->file->getHandler(['mode'=>'r']);

				while(FALSE !== ($char=$handler->fgetc())){

					$count++;

				}

				return $count;

			}

			public function substr($parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters,'start');
				$parameters->findInsert('length',NULL);

				$start	=	$parameters->find('start')->toInt()->valueOf();
				$length	=	NULL;

				if(!is_null($parameters->find('length')->valueOf())){

					$length	=	(int)$parameters->find('length')->valueOf();

				}

				$size			=	$this->strlen();

				if($start>$size){

					return FALSE;

				}

				$count	=	0;

				if($start < 0){

					$start	=	$size+$start;

					if($start<0){

						$start	=	0;

					}

				}


				$substr			=	new File(['tmp'=>TRUE,'ontostring'=>'contents']);
				$substrHandler	=	$substr->getHandler(['mode'=>'w']);

				$handler	=	$this->file->getHandler();
				$handler->fseek($start);

				if(!($length===NULL)){

					if($length<0){

						$count	=	($length*-1)+1;
						$length	=	$length<0	?	($size-$start)+1	:	$length;

					}

					if($length<0){

						$length	=	0;

					}

				}

				if($start>=$size){

					return FALSE;

				}

				while(FALSE !== ($char=$handler->fgetc())){

					if(!($length===NULL) && ++$count>$length){

						break;

					}

					$substrHandler->fwrite($char);

				}

				$substrHandler->fseek(0);

				return new static($substr);

			}

			public function strpos($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters,'needle');

				$needle		=	$parameters->demand('needle')->valueOf();
				$offset		=	$parameters->find('offset',0)->valueOf();

				if($offset<0){

					throw new \InvalidArgumentException("Offset can not be negative");

				}

				$handler		=	$this->file->getHandler();
				$handler->fseek($offset);

				if($handler->eof()){

					$msg	=	"Offset \"$offset\" does not exists\n";
					throw new \InvalidArgumentException();

				}

				$length	=	(int)StringUtil::length($needle)->valueOf();

				$str		=	'';
				$count	=	$offset;
				$found	=	FALSE;

				$needle	=	StringUtil::substr($needle,['start'=>0,'end'=>1]);

				while(FALSE !== ($str=$handler->fgetc())){

					if($str==$needle){
						$found	=	TRUE;
						break;
					}

					$count++;

				}

				return $found	?	$count	:	FALSE;

			}

			public function strrpos($needle){

				$needle	=	VarUtil::printVar($needle);
				$handler	=	$this->file->getHandler();

				$needle	=	StringUtil::substr($needle,['start'=>0,'end'=>1]);
				$handler->fseek(0);

				while(FALSE!==($char=$handler->fgetc())){

					if($needle==$char){

						$found=$handler->ftell()-1;

					}

				}

				return $found;
				
			}

			public function __toString(){

				return sprintf('%s',$this->file->getContents());

			}

		}

	}
