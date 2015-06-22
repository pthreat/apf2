<?php

	namespace apf\io\os\unix{

		use apf\type\base\Vector									as	VectorType;
		use apf\type\util\common\Variable						as	VarUtil;

		use apf\io\os\unix\File										as	File;
		use apf\io\os\unix\directory\Handler					as	DirectoryHandler;
		use apf\io\os\unix\util\Directory						as	DirUtil;

		use apf\io\common\exception\directory\Invalid		as	NotADirectoryException;
		use apf\io\common\exception\directory\CantCreate	as	CanNotCreateDirectoryException;
		use apf\io\common\exception\directory\NotWritable	as	DirectoryNotWritableException;
		use apf\io\common\exception\file\NotFound				as	FileNoutFoundException;
		use apf\io\common\exception\file\NotWritable			as	FileNotWritableException;

		//Extends to file type and due to this it already implements the TypeInterface
		class Directory extends File implements \Iterator{

			public static function instance($val,$parameters=NULL){

				if(is_a($val,__CLASS__)){

					return $val;

				}

				$dir	=	new static($val,$parameters);

				return $dir;

			}

			public function copy($parameters=NULL){

				DirUtil::copy($this);

			}

			public function find($file,$parameters=NULL){

				$file	=	VarUtil::printVar($file);

				foreach($this as $curFile){

					if($curFile->getFileName() == $file){

						return $curFile;

					}

				}

				throw new FileNoutFoundException("$file was not found");

			}

			public function chmod($perms){

				try{

					parent::chmod($perms);

				}catch(FileNotWritableException $e){

					throw new DirectoryNotWritableException("Directory $this is not writable");

				}

				return $this;

			}

			public function create($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				DirUtil::create(sprintf('%s',$this),$parameters);
				return $this;

			}

			public function ls(){

				$ls	=	Array();

				foreach($this->getHandler() as $file){

					$owner		=	$file->getOwner();
					$group		=	$file->getGroup();
					$perms		=	$file->getPerms()->toString();
					$name			=	sprintf('%s',$file->getFileName());

					$ls[$name]	=	sprintf("%s\t%s\t%s\t%s\n",$perms,$owner,$group,$name);

				}

				ksort($ls);

				return implode('',$ls);

			}

			public function getHandler($parameters=NULL){

				if(!is_null($this->handler)){

					return $this->handler;

				}

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				$handler		=	new DirectoryHandler($this->getRealPath());
				$handler->setParameters($parameters);

				return $this->handler	=	$handler;

			}

			public function delete(){

				return rmdir("$this");

			}

			public function __destruct(){

				if($this->isTemporary()){
					echo "delete\n";
				}

			}

		}

	}

