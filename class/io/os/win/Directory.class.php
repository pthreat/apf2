<?php

	namespace apf\io\os\win{

		use apf\type\base\Vector									as	VectorType;
		use apf\type\util\common\Variable						as	VarUtil;

		use apf\io\os\win\File										as	File;
		use apf\io\os\win\directory\Handler					as	DirectoryHandler;

		use apf\io\common\exception\directory\Invalid		as	NotADirectoryException;
		use apf\io\common\exception\directory\CantCreate	as	CanNotCreateDirectoryException;
		use apf\io\common\exception\directory\NotWritable	as	DirectoryNotWritableException;
		use apf\io\common\exception\file\NotWritable			as	FileNotWritableException;

		//Extends to file type and due to this it already implements the TypeInterface
		class Directory extends File implements \Iterator{

			public function __construct($val,$parameters=NULL){

				$val	=	VarUtil::printVar($val,$parameters);

				parent::__construct($val,$parameters);

				if(!$this->isDir()){

					$msg	=	"Given value \"$val\" is NOT a directory";
					throw new NotADirectoryException($msg);

				}

				//Defined in parent Type trait
				$parameters	=	$this->parseParameters($parameters);

				if($this->parameters->find('mustExist',FALSE)->getValue()){

					$this->exists($this->parameters);

				}

				$chmod	=	$parameters->find('chmod',FALSE)->getValue();

				if($chmod){

					$this->chmod($chmod);

				}

				$temporary	=	$parameters->findOneOf('tmp','temp','temporary','tmpdir',FALSE);

				if($temporary->getValue()){

					$this->setTemporary(TRUE);

				}

				$mustExist	=	$parameters->find('exists',FALSE);

				if($mustExist&&!$this->exists()){

					$msg	=	"Specified directory \"{$this->value}\" doesn't exists";
					throw new DirectoryNotFoundException($msg);

				}

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

				throw new \Exception("$file was not found");

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
				DirUtil::create("$this",$parameters);
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

			/******************************************************
			*Iterator interface methods
			*/

			public function current(){

				return $this->getHandler($this->parameters)->current();

			}

			public function key(){

				return $this->getHandler()->key();

			}

			public function rewind(){

				return $this->getHandler()->rewind();

			}

			public function next(){

				return $this->getHandler()->next();

			}

			public function valid(){

				return $this->getHandler()->valid();

			}

			/*End of iterator interface methods
			 *****************************************************/


			public function toArray(){

				return $this->getHandler()->toArray();

			}

			public function delete(){

				return rmdir("$this");

			}

			public function __toString(){

				return sprintf('%s',$this->getRealPath());

			}

			public function __destruct(){

				if($this->isTemporary()){
					echo "delete\n";
				}

			}

		}

	}

