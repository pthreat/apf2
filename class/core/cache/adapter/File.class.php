<?php

	namespace apf\core\cache\adapter{

		use apf\core\Cache;
		use apf\core\cache\adapter\file\Entry	as	CacheEntry;
		use apf\core\Log;
		use apf\io\Directory;
		use apf\io\File								as	FSFile;
		use apf\type\util\common\Variable		as	VarUtil;
		use apf\type\base\IntNum					as	IntType;
		use apf\type\base\Vector					as	VectorType;
		use apf\type\util\base\Str					as	StringUtil;
		use apf\type\collection\common\Disk		as	DiskCollection;

		use apf\type\parser\Parameter				as	ParameterParser;

		class File extends Cache{

			private	$fileATime	=	NULL;
			
			public function __construct($parameters=NULL){

				$parameters			=	ParameterParser::parse($parameters);
				$defaultCacheDir	=	sprintf('%s%s%s',sys_get_temp_dir(),DIRECTORY_SEPARATOR,'__apf_cache');
				$cacheDir			=	VarUtil::printVar($parameters->find('cachedir',$defaultCacheDir)->valueOf());
				$this->setSource($cacheDir);

				parent::__construct($parameters);

			}

			//Cache directory
			public function setSource($dir=NULL){

				$directory		=	Directory::instance($dir);

				if(!$directory->exists()){

					$directory->create();

				}

				$this->source	=	$directory;	

				return $this;

			}

			public function get($name,$getTTL=FALSE){

				$name		=	$this->makeCacheName($name);
				$cache	=	unserialize(sprintf('%s',$this->source->find($name)->getContents()));
				$entry	=	new CacheEntry($cache);

				return $entry;

			}

			private function makeCacheName($name){

				$name	=	VarUtil::printVar($name);
				return StringUtil::match($name,['match'=>'.cache'])	?	$name	:	sprintf('%s.cache',$name);

			}

			public function store($name,$value,$ttl=0){

				$name		=	$this->makeCacheName($name);
				$file		=	sprintf('%s%s%s',$this->source,DIRECTORY_SEPARATOR,$name);
				$file		=	new FSFile($file);
				$ttl		=	IntType::cast($ttl)->valueOf();
				$exists	=	$file->exists();

				if($exists){

					//Check if cache time has expired
					$cachedValue	=	$this->get($name);

					if($ttl==-1){

						$cachedValue->delete($name);

					}

					//if it hasn't, return FALSE
					if($cachedValue->getTTL()==0 || ((time()-$file->getAtime())<$cachedValue->getTTL())){

						$this->logDebug("Cache hit: $cachedValue");
						return FALSE;

					}

					$this->logDebug("Expired cache: $cachedValue");
					$cachedValue->delete();

				}

				$file->create();

				if(is_callable($value)){

					$value	=	$value();

				}

				$cache	=	['ttl'=>$ttl,'value'=>$value,'name'=>$name,'source'=>sprintf('%s',$this->source)];
				$handler	=	$file->getHandler(['mode'=>'a+']);
				$handler->fwrite(serialize($cache));
				$cachedValue	=	new CacheEntry($cache);
				$this->logDebug("Add cache: $cachedValue");

				return $this;

			}

			public function delete($name){

				$name		=	$this->makeCacheName($name);
				return $this->source->find($name)->delete();

			}

			public function listEntries(){

				$entries	=	Array();

				foreach($this->source->getHandler() as $file){

					if($file->isDir()){

						continue;

					}

					$entries[]	=	new CacheEntry(unserialize($file->getContents()));

				}

				return $entries;

			}

			public function info(){

				$handler	=	$this->source->getHandler();

				return VectorType::cast([
													"entries"=>$handler->count(),
													"size"	=>$handler->size()
				]);

			}

			public function isCached($name=NULL){

				try{

					$this->source->find($name);
					return TRUE;

				}catch(\Exception $e){
					
					return FALSE;

				}

			}

		}

	}
