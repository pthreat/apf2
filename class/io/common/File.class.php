<?php

	namespace apf\io\common{

		use apf\type\base\Str										as	StringType;
		use apf\type\base\Vector									as VectorType;
		use apf\type\base\Boolean									as	BooleanType;
		use apf\type\base\IntNum									as	IntType;
		use apf\type\parser\Parameter								as	ParameterParser;

		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\util\base\Str									as	StringUtil;

		use apf\io\common\exception\file\NotFound				as	FileNotFoundException;
		use apf\io\common\exception\file\NotWritable			as	FileNotWritableException;
		use apf\io\common\exception\file\NotReadable			as	FileNotReadableException;
		use apf\io\common\exception\file\NotARegularFile	as	NotARegularFileException;

		use apf\io\util\Directory									as	DirectoryUtil;
		use apf\io\util\File											as	FileUtil;

		use apf\iface\Convertible									as	ConvertibleInterface;
		use apf\iface\io\File										as	FileInterface;

		abstract class File extends \SplFileInfo implements ConvertibleInterface,FileInterface,\Iterator{

			//This trait defines protected $parameters
			use \apf\traits\type\parser\Parameter;

			protected	$fetchTimes		=	NULL;
			protected	$isTmp			=	NULL;
			protected	$perms			=	NULL;
			protected	$exists			=	NULL;
			protected	$handler			=	NULL;
			protected	$file				=	NULL;
			protected	$onToString		=	NULL;

			public function __construct($parameters=NULL){

				parent::setFileClass('apf\io\file\Handler');

				//Assume the user has entered /path/to/file.txt
				if(is_string($parameters)){

					return parent::__construct($parameters);

				}

				$this->parseParameters($parameters);

				$parameters	=	ParameterParser::parse($parameters);

				$isTmp		=	(bool)$parameters->findOneOf('tmp','temporary','tmpfile','temp',FALSE)->valueOf();

				$fileName	=	NULL;

				if($isTmp){

					if(!(boolean)$parameters->find('keep',FALSE)->valueOf()){

						$this->isTmp	=	TRUE;

					}

					$ds				=	DIRECTORY_SEPARATOR;
					$tmpDir			=	VarUtil::printVar($parameters->find('tmpdir',\sys_get_temp_dir()));
					$tmpPrefix		=	VarUtil::printVar($parameters->find('tmpprefix','__apf'));
					$parameters->replace('mode','a+');

					//Check for temporary file name collisions, HIGHLY unlikely, but we are ready 
					//for it nevertheless

					do{

						$tmpName		=	sha1(uniqid());
						$file			=	sprintf('%s%s%s%s',$tmpDir,$ds,$tmpPrefix,$tmpName);

					}while(file_exists($file));

					FileUtil::create($file);

					$parameters->replace('file',$file);

				}else{

					try{

						$file	=	$parameters->demand('file')->valueOf();

					}catch(\Exception $e){

						throw new \InvalidArgumentException("Must specify file name");

					}

				}

				$this->onToString	=	VarUtil::printVar($parameters->find('ontostring','path')->valueOf());

				parent::__construct($file);

			}

			public function md5($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters);
				return FileUtil::md5($this,$parameters);

			}

			public function sha1($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters);
				$parameters->replace('cast',FALSE);

				return FileUtil::sha1("$this",$parameters);

			}

			public function getName(){

				return parent::getFileName();

			}

			/**
			*Check if the current file exists
			*@return boolean TRUE File exists
			*@return boolean FALSE File doesn't exists
			*/

			public function exists($parameters=NULL){

				$parameters	=	ParameterType::parse($parameters);
				$mustExist	=	$parameters->find('mustExist',FALSE);

				if(!is_null($this->exists)){

					if($mustExist){

						ValidateFile::mustExist("$this");
						return  TRUE;

					}

					return $this->exists;

				}

				if($mustExist){

					ValidateFile::mustExist($this->getRealPath());
					return $this->exists	=	TRUE;

				}

				return $this->exists	=	ValidateFile::exists("$this");

			}

			/**
			*Method			:	isTemporary
			*Description	:	Check if the current file instance is a temporary file
			*	
			*@return	boolean	TRUE	File is temporary
			*@return	boolean	FALSE	File is not temporary
			*/

			public function isTemporary(){

				return $this->isTmp;

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

			public function close(){

				$this->handler	=	NULL;
				return $this;

			}

			/**
			*Method			:	getDirectory
			*Description	:	Gets you a Directory instance where this file is located
			*@return apf\type\Directory An apollo Directory type
			*/

			public function getDirectory(){

				return Directory::cast(dirname("$this"));

			}

			public function ls(){

				$owner	=	$this->getOwner();
				$group	=	$this->getGroup();
				$perms	=	$this->getPerms()->toString()->valueOf();

				return sprintf("%s\t%s\t%s\t%s",$perms,$owner,$group,$this->getFileName());

			}

			/**
			*Method			:	chunkedOutput
			*Description	:	Print a file to stdout in small chunks this is useful when you want to 
			*output a very large file.
			*
			*@param int chunksize how many data we should read for each iteration
			*until we reach EOF.
			*
			*@return NULL This method does not has a return value
			*
			*/

			public function chunkedOutput($chunkSize=1024){

				while($line=$this->read($chunkSize)){

					echo VarUtil::printVar($line);

				}

			}

			public function toStdOut($bytes=1024){

				$bytes	=	IntType::cast($bytes)->valueOf();
				$handler	=	$this->getHandler(['mode'=>'r']);
				$handler->fseek(0);

				while(FALSE !== ($chars=$handler->fread($bytes))){

					echo $chars;

				}

			}


			/*******************************************************
			*Abstract methods, any file platform class must have 
			*these in order to be valid.
			*/

			abstract public function chmod($perms);
			abstract public function getHandler($parameters=NULL);
			abstract public function copy($parameters=NULL);
			abstract public function move($parameters=NULL);
			abstract public function delete();
			abstract public function isOwnedBy();
			abstract public function isOwnerReadable();
			abstract public function isOwnerWritable();
			abstract public function isOwnerExecutable();
			abstract public function isGroupReadable();
			abstract public function isGroupWritable();
			abstract public function isGroupExecutable();
			abstract public function isWorldReadable();
			abstract public function isWorldWritable();
			abstract public function isWorldExecutable();

			/*
			*End of abstract methods
			********************************************************/

			/**
			*Method			:	getFile
			*Description	:	Gets the current file, with the full path. 
			*						This method will get you the file with the full path. If you want 
			*						the file name only, see getFileName.
			*
			*@see self::getFileName
			*@return	\apf\type\Str	An Apollo String type containing the full file with the path
			*/

			public function getFile(){

				return StringType::cast("$this");

			}

			/**
			*Method			:	getFileName
			*Description	:	Gets the current file NAME. This method will not get you the full path
			*						if you want the file with the full path, see getFile instead
			*@return	\apf\type\Str	An Apollo String type containing the file name
			*/

			public function getFileName(){

				return StringType::cast(basename("$this"));

			}

			/**
			*Method			:	getContents
			*Description	:	Gets the file contents as an Apollo string type
			*						You should AVOID this method if possible, try using
			*						foreach on the File instance instead.
			*
			*@return	\apf\type\Str	An Apollo String type with the file contents.
			*/

			public function getContents(){

				$contents	=	file_get_contents($this->getRealPath());
				return StringType::cast($contents);

			}

			public function jsonSerialize(){

				return $this->getContents()->valueOf();

			}

			/************************************************************
			*Type conversion methods
			*/

			/**
			*Method			:	toBoolean
			*Description	:	In this case, toBoolean will check if the file exists or not
			*
			*@return	\apf\type\Boolean	Apollo Boolean type
			*/

			public function toBoolean(){

				return BooleanType::cast($this->exists());

			}

			/**
			*Method			:	toArray
			*Description	:	Transform file contents to an Array, in case of large files 
			*						Usage of this method is discouraged in case of handling large files.
			*
			*@return	Vector	an Apollo Vector type containing the file contents.
			*/

			public function toArray(){

				return VectorType::cast(file("$this"));

			}

			/**
			*MUST GIVE STRING COLLECTION
			*Method			:	toString
			*						
			*Description	:	Transform the file into an Apollo String
			*						This method is DIFFERENT from the magic method __toString
			*						the given method will get you the file contents as an Apollo String
			*						the magic method __toString will get you the file name instead 
			*						of the file contents.
			*						Usage of this method is discouraged in case of handling large files.
			*
			*@return \apf\type\Str	Apollo string type
			*/

			public function toString(){

				return StringType::cast($this->getContents());

			}

			public function toReal(){
			}

			public function toChar(){
			}


			/**
			*To int will return the file size in bytes as an IntNum type
			*/
			public function toInt(){

				return IntType::cast($this->getSize());

			}

			/**
			*Method			:	toJSON
			*Description	:	Transform the file contents into an Apollo JSON type
			*						Usage of this method is discouraged in case you're handling large files.
			*
			*@return \apf\type\JSON	Apollo JSON type
			*/

			public function toJSON(){

				return JSONType::cast($this->getContents())->encode();

			}

			/**
			*Method			:	__toString
			*Description	:	Magic PHP __toString. Will give you the file name
			*
			*@return	String	The file name
			*/

			public function __toString(){

				if($this->onToString=='contents'){

					return file_get_contents($this->getRealPath());

				}

				$realPath	=	$this->getRealPath();

				if(empty($realPath)){

					return parent::__toString();

				}

				return $realPath;

			}

			/*End of type conversion methods
			 **************************************************************/

			/**
			*Method			:	__destruct
			*Description	:	The destructor closes the file pointer, if the file is set as temporary
			*						it will also delete the file.
			*/

			public function __destruct(){

				if(!is_null($this->handler)){

					$this->close();

				}

				if($this->isTmp){

					try{

						$this->delete();

					}catch(\Exception $e){

					}

				}

			}

		}

	}

