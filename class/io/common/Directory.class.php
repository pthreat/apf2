<?php

	namespace apf\io\common{

		use apf\io\common\File;
		use apf\io\directory\Handler								as	DirectoryHandler;
		use apf\type\base\Vector									as	VectorType;
		use apf\type\util\common\Variable						as	VarUtil;

		use apf\io\common\exception\directory\Invalid		as	NotADirectoryException;
		use apf\io\common\exception\directory\CantCreate	as	CanNotCreateDirectoryException;
		use apf\io\common\exception\directory\NotWritable	as	DirectoryNotWritableException;

		use apf\iface\io\Directory							as	DirectoryInterface;

		//Extends to file type and due to this it already implements the TypeInterface

		abstract class Directory extends File implements \Iterator,DirectoryInterface{

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

			public function find($file,$parameters=NULL){

				$file	=	VarUtil::printVar($file);

				foreach($this as $curFile){

					if($curFile->getFileName() == $file){

						return $curFile;

					}

				}

				throw new \Exception("$file was not found");

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

