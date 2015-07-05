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

			private	$file			=	NULL;
			private	$rHandler	=	NULL;
			private	$curChar		=	NULL;
			private	$position	=	0;

			public function __construct($string,$parameters=NULL){

				$this->parseParameters($parameters);
	
				if($string instanceof File){

					$this->file	=	$string;

				}else{

					$this->file	=	new File(['tmp'=>TRUE]);

				}

				$handler		=	$this->file->getHandler(['mode'=>'w']);
				$handler->fwrite($string);
				$handler->fflush();

				clearstatcache(TRUE,$this->file);

				$handler				=	$this->file->getHandler(['mode'=>'r','reopen'=>TRUE]);
				$this->rHandler	=	$handler;

			}

			public static function cast($value,$parameters=NULL){

				return new static($value,$parameters);

			}

			public static function instance($parameters=NULL){

				return new static("");

			}

			public function offsetGet($offset){

				$offset	=	(int)$offset;
				$count	=	0;
				$this->rHandler->fseek(0);

				do{

					$char	=	$this->rHandler->fgetChar();

				}while($count++<$offset);

				if($char===FALSE){

					throw new UndefinedOffsetException("Offset $offset doesn't exists");

				}

				return $char;

			}

			public function offsetSet($offset,$value){

				$offset	=	!is_null($offset) ? (int)$offset : ($this->strlen() + 1);

				$file		=	new File(['tmp'=>TRUE]);
				$handler	=	$file->getHandler(['mode'=>'w']);

				foreach($this as $key=>$char){

					if($key==$offset){

						$char	=	CharType::cast($value);	

					}

					$handler->fwrite($char);

				}

				$this->file			=	$file;
				$this->rHandler	=	$file->getHandler(['mode'=>'r','reopen'=>TRUE]);
				$this->rHandler->fseek(0);

				return;

			}

			public function offsetExists($offset){

				$offset	=	(int)$offset;

				return $offset >=0 && $offset <= $this->strlen();

			}

			public function offsetUnset($offset){

				$offset	=	(int)$offset;

				$file		=	new File(['tmp'=>TRUE]);
				$handler	=	$file->getHandler(['mode'=>'w']);

				foreach($this as $key=>$char){

					if($key==$offset){

						continue;

					}

					$handler->fwrite($char);

				}

				$this->file			=	$file;
				$this->rHandler	=	$file->getHandler(['mode'=>'r','reopen'=>TRUE]);
				$this->rHandler->fseek(0);

				return;

			}

			public function current(){

				return $this->curChar	=	$this->rHandler->fgetChar($forwardCursor=FALSE);

			}

			public function next(){

				return $this->curChar	=	$this->rHandler->fgetChar();

			}

			public function rewind(){

				$this->position	=	0;
				return $this->rHandler->fseek(0);

			}

			public function valid(){

				return $this->rHandler->fgetChar($forwardCursor=FALSE);

			}

			public function key(){

				return $this->position++;

			}

			public function toChar(){
			}

			public function strlen(){

				return $this->rHandler->getLength();

			}

			//Your if's are many in this one young padawan
			//but fear not ...!
			//http://lxr.php.net/xref/PHP_5_6/ext/standard/string.c line 2240

			public function substr($parameters=NULL){

				$pos			=	$this->rHandler->ftell();

				$parameters	=	ParameterParser::parse($parameters,'start');
				$parameters->findInsert('length',NULL);

				$start	=	$parameters->find('start')->toInt()->valueOf();
				$length	=	NULL;

				if(!is_null($parameters->find('length')->valueOf())){

					$length	=	(int)$parameters->find('length')->valueOf();

				}

				$size	=	$this->strlen();

				if($start>$size){

					return FALSE;

				}

				if(is_null($length)){

					$length	=	$size;

				}

				$substr			=	new File(['tmp'=>TRUE,'ontostring'=>'contents']);
				$substrHandler	=	$substr->getHandler(['mode'=>'w']);

				if($start>=$size){

					return FALSE;

				}
				if(-$start>$size){

					$start	=	-$size;

				}

				if(($start<0&&$length<0)){ 

					if($length == $start){

						return "";

					}

					if($start > $length){

						return "";

					}


					$length	=	(-$start - -$length);


				}

				if($start<0&&-$start>$size){

					$start	=	0;

				}

				if($start > 0 && $length<0){

					$length	=	-$length;

				}

				$count	=	0;

				$length	=	$length<0	?	$length*-1	:	$length;

				if($start<0){

					$start	=	($start*-1)>=$size	?	0	:	$start;

				}

				$handler	=	$this->rHandler;
				$handler->fseekChar($start);

				while(FALSE !== ($char=$handler->fgetChar())&&$count++<$length){

					$substrHandler->fwrite($char);

				}

				$substrHandler->fseek(0);

				return new static($substr);

			}

			public function strpos($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters,'needle');

				$needle		=	$parameters->demand('needle')->valueOf();
				$offset		=	$parameters->find('offset',0)->valueOf();
				$ftell		=	$this->rHandler->ftell();

				if($offset<0){

					throw new \InvalidArgumentException("Offset can not be negative");

				}

				$handler		=	$this->rHandler;

				$handler->fseek(0);
				$handler->fseekChar($offset);

				if($handler->eof()){

					$msg	=	"Offset \"$offset\" does not exists\n";
					throw new \InvalidArgumentException();

				}

				$length	=	(int)StringUtil::length($needle)->valueOf();

				$str		=	'';
				$count	=	$offset;
				$found	=	FALSE;

				$needle	=	StringUtil::substr($needle,['start'=>0,'end'=>1]);

				while(FALSE !== ($str=$handler->fgetChar())){

					if($str==$needle){
						$found	=	TRUE;
						break;
					}

					$count++;

				}

				$this->rHandler->fseek($ftell);

				return $found	?	$count	:	FALSE;

			}

			public function strrpos($needle){

				$needle	=	VarUtil::printVar($needle);
				$handler	=	$this->rHandler;

				$needle	=	StringUtil::substr($needle,['start'=>0,'end'=>1]);
				$handler->fseek(0);

				while(FALSE!==($char=$handler->fgetChar())){

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
