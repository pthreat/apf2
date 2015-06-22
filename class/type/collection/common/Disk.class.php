<?php

	namespace apf\type\collection\common{

		use apf\io\File;
		use apf\type\Common											as	Type;
		use apf\type\collection\Common							as	CommonCollection;
		use apf\type\exception\base\vector\UndefinedIndex	as	UndefinedIndexException;

		class Disk extends CommonCollection{

			private	$offset				=	0;
			private	$file					=	NULL;
			private	$handler				=	NULL;
			private	$recordSeparator	=	NULL;
			private	$key					=	NULL;
			private	$useSerialize		=	FALSE;
			private	$onGet				=	NULL;

			public function __construct($parameters=NULL){

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

				$this->key	=	NULL;
				$this->handler->fseek(0);

			}

			public function current(){

				$record	=	"";

				while(FALSE !== ($char=$this->handler->fgetc())){

					if($char->valueOf()===$this->recordSeparator){

						$value		=	$this->useSerialize	?	unserialize($record)	:	json_decode($record,$assoc=TRUE);
						$this->key	=	substr(key($value),1);
						$this->handler->fseek($this->handler->ftell()-1);

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
	
				if($this->handler->ftell()===1){

					return;

				}

				return $this->handler->seekToChar($this->recordSeparator);

			}

			public function valid(){

				return !$this->handler->eof();

			}

			public function offsetSet($offset,$value){

				if(is_null($offset)){

					$offset	=	$this->offset;
					$this->offset++;

				}

				$piece	=	Array("i$offset"=>$value);

				if($this->useSerialize){

					$this->handler->fwrite(serialize($piece).$this->recordSeparator);
					return;

				}

				$this->handler->fwrite(json_encode($piece).$this->recordSeparator);

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

		}

	}
