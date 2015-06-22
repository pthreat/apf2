<?php

	namespace apf\type\base{

		use apf\io\File;
		use apf\type\Common											as	Type;
		use apf\type\base\Vector									as	VectorType;
		use apf\type\base\VectorCommon;
		use apf\type\util\base\Str									as	StringUtil;
		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\util\base\Vector								as	VectorUtil;
		use apf\type\exception\base\vector\UndefinedIndex	as	UndefinedIndexException;
		use apf\type\parser\Parameter								as	ParameterParser;

		use apf\type\exception\base\vector\ValueNotFound	as	ValueNotFoundException;

		class DiskVector extends VectorCommon{

			private	$offset				=	0;
			private	$size					=	0;
			private	$file					=	NULL;
			private	$handler				=	NULL;
			private	$recordSeparator	=	NULL;
			private	$key					=	NULL;
			private	$useSerialize		=	FALSE;
			private	$onGet				=	NULL;

			protected function __construct($value=Array(),$parameters=NULL){

				$this->parseParameters($parameters);

				$this->recordSeparator	=	$this->parameters
				->find('recordSeparator',chr(30))
				->toChar(['strict'=>TRUE])
				->valueOf();

				$this->parameters->findInsert('useSerialize',FALSE);
				$this->parameters->findInsert('autoCast',TRUE);
				$this->useSerialize	=	(boolean)$this->parameters->find('useSerialize')->valueOf();
				$this->autoCast		=	(boolean)$this->parameters->find('autoCast')->valueOf();

				$onGet	=	$this->parameters->find('onget')->valueOf();

				if($onGet){
				
					if(!is_callable($onGet)){

						throw new \InvalidArgumentException("onget parameter only accepts callbacks");

					}

					$this->onGet	=	$onGet;

				}

				$this->file		=	new File($this->parseParameters(['tmp'=>TRUE],$merge=FALSE));
				$this->handler	=	$this->file->getHandler();

				$value			=	VectorType::cast($value,$parameters);

				foreach($value as $key=>$val){

					$this->offsetSet($key,$val);

				}

			}

			public static function cast($value,$parameters=NULL){

				return new static($value,$parameters);

			}

			public static function instance($parameters=NULL){

				return self::cast([],$parameters);

			}

			public function setRecordSeparator($separator){

				$this->recordSeparator	=	CharType::cast($separator)->valueOf();

			}

			public function getRecordSeparator(){

				return $this->recordSeparator;

			}

			public function key(){

				return $this->key;

			}

			public function rewind(){

				$this->offset	=	0;
				$this->handler->fseek(0);

			}

			public function current(){

				$record		=	"";
				$currentPos	=	$this->handler->ftell();

				while(FALSE !== ($char=$this->handler->fgetc())){

					if($char->valueOf()===$this->recordSeparator){

						//get back to the current position
						//THIS IS WHAT CAUSES TO AN INFINITE LOOP
						//REVIEW!!!!

						$this->handler->fseek($currentPos);

						$value		=	$this->useSerialize	?	unserialize($record)	:	json_decode($record,$assoc=TRUE);
						$this->key	=	substr(key($value),1);

						if(!is_null($this->onGet)){

							$callBack	=	&$this->onGet;
							return $callBack(array_values($value)[0]);

						}

						return $this->autoCast	?	Type::castAny(array_values($value)[0])	:	array_values($value)[0];

					}

					$record	=	sprintf('%s%s',$record,$char);

				}

			}

			public function next(){
	
				$this->handler->seekToChar($this->recordSeparator);
				return $this->current();

			}

			public function valid(){

				$this->handler->fgetc();

				if($this->handler->eof()){

					return FALSE;

				}

				$this->handler->fseek($this->handler->ftell()-1);

				return TRUE;

			}

			public function offsetSet($offset,$value){

				if(is_null($offset)){

					$offset	=	$this->offset;

				}

				$piece		=	Array("i$offset"=>&$value);

				if($this->useSerialize){

					$this->handler->fwrite(serialize($piece).$this->recordSeparator);
					return;

				}

				$this->handler->fwrite(json_encode($piece).$this->recordSeparator);

				$this->offset++;
				$this->size++;

			}

			public function offsetGet($offset){

				$this->handler->fseek(0);

				$recordSeparator	=	$this->recordSeparator;
				$record				=	"";

				while(FALSE !== ($char=$this->handler->fgetc())){

					if($char->valueOf()!==$recordSeparator){

						$record	= sprintf('%s%s',$record,$char);
						continue;

					}

					$record	=	$this->useSerialize	?	unserialize($record)	:	json_decode($record,$assoc=TRUE);

					if(array_key_exists("i$offset",$record)){

						return $this->autoCast	?	Type::castAny($record["i$offset"])	:	$record["i$offset"];

					}

					$record="";

				}

				throw new UndefinedIndexException("Undefined index $offset");

			}

			public function offsetExists($offset){

				return (boolean)$this->offsetGet($offset);

			}

			public function offsetUnset($offset){

				$newFile		=	new File(['tmp'=>TRUE]);
				$newHandler	=	$newFile->getHandler();

				$this->handler->fseek(0);

				$recordSeparator	=	$this->recordSeparator;
				$record				=	"";
				$found				=	FALSE;

				while(FALSE !== ($char=$this->handler->fgetc())){

					if($char->valueOf()!==$recordSeparator){

						$record	= sprintf('%s%s',$record,$char);
						continue;

					}

					$tmpRecord	=	$this->useSerialize	?	unserialize($record)	:	json_decode($record,$assoc=TRUE);

					if(array_key_exists("i$offset",$tmpRecord)){

						$found=TRUE;
						$record="";
						continue;

					}

					$newHandler->fwrite(sprintf('%s%s',$record,$this->recordSeparator));
					$record="";

				}

				if(!$found){

					throw new UndefinedIndexException("Index $offset doesn't exists");

				}

				$newHandler->fseek(0);
				$this->handler	=	$newHandler;

			}

			public function size(){

				return $this->size;

			}

			public function toChar(){
			}

			public function sort($parameters=NULL){

				$size		=	$this->size;
				$sorted	=	new static();

				for($i=0;$i< $size;$i++){

					foreach($this as $key=>$value){
					
						$next	=	$this->next();
						$nkey	=	$this->key();

						echo $key.'|'.$nkey."\n";

						if($value>$next){

							$sorted[]		=	$value;
							$this[$key]		=	$next;
							$this[$nkey]	=	$value;

						}

					}

				}

				return $sorted;

			}

			public function natSort(){
			}

			public function asort(){
			}

			public function rsort(){
			}

			public function ksort(){

				foreach($this as $k=>$val){
				}

			}

			public function indexOf($value){

				return VectorUtil::indexOf($this,$value);

			}

			public function shuffle(){

				$file	=	new File(['tmp'=>TRUE]);


			}

			public function implode($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$separator	=	VarUtil::printVar($parameters->find('separator','')->valueOf());

				$size		=	$this->size();
				$count	=	1;
				$file		=	new File(['tmp'=>TRUE,'ontostring'=>'contents']);
				$handler	=	$file->getHandler(['mode'=>'a+']);

				foreach($this as $value){

					if($count==$size){

						$separator='';

					}

					$value	=	sprintf('%s%s',$value,$separator);
					$handler->fwrite(VarUtil::printVar($value));
					$count++;

				}

				return $file;

			}

			public function shift(){

				foreach($this as $key=>$value){
					break;
				}

				$this->offsetUnset($key);

				return $value;

			}

			public function flip(){
			}

			public function pop(){
			}

			public function reverse(){
			}

			public function getFile(){

				return $this->file;

			}

			public function __toString(){

				$this->file->getHandler()->fseek(0);

				return $this->file->getContents()->valueOf();

			}

		}

	}
