<?php

	namespace apf\io\os\unix {

		use apf\type\base\Str										as	StringType;
		use apf\type\base\Vector									as VectorType;
		use apf\type\base\Boolean									as	BooleanType;
		use apf\type\base\IntNum									as	IntType;

		use apf\type\parser\Parameter								as	ParameterParser;
		use apf\io\os\unix\parser\Permission;

		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\base\Str										as	StringUtil;

		use apf\io\common\File										as	CommonFile;

		use apf\acl\os\unix\User;
		use apf\acl\os\unix\Group;

		use apf\io\os\unix\util\File								as	FileUtil;
		use apf\io\os\unix\validate\File							as	ValidateFile;
		use apf\io\os\unix\validate\Directory					as	ValidateDir;
		use apf\io\os\unix\util\Directory						as	DirectoryUtil;

		use apf\io\common\exception\file\NotFound				as	FileNotFoundException;
		use apf\io\common\exception\file\NotWritable			as	FileNotWritableException;
		use apf\io\common\exception\file\NotReadable			as	FileNotReadableException;
		use apf\io\common\exception\file\NotARegularFile	as	NotARegularFileException;

		class File extends CommonFile{

			public function create($parameters=NULL){

				return FileUtil::create(sprintf('%s',$this),$parameters);

			}

			public function chmod($perms){

				$parameters	=	$this->parseParameters(['perms'=>$perms],$merge=TRUE);
				FileUtil::chmod("$this",$parameters);
				return $this;

			}

			/**
			*Check if the current file exists
			*@return boolean TRUE File exists
			*@return boolean FALSE File doesn't exists
			*/

			public function exists($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$mustExist	=	(boolean)$parameters->find('mustExist',FALSE)->valueOf();

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
			*Method			:	Get Handler
			*Description	:	Get a file handler for the current file
			*Defaults		:	By default the file will be open in a+ mode if no mode is specified 
			*						that is read and write.
			*
			*@param mixed $options 
			*
			*Parameters		:  
			*
			*mode	parameter:	Specify one of r,ro,read,readonly if you want to open the file in read mode.
			*						Specify one of w or write if you want to open the file in writing mode.
			*						Specify one of a+,append,rw or readwrite if you'd like to open the file in 
			*						read+write mode
			*
			*reopen parameter:
			*						If set to TRUE (or any other value ressembling TRUE) file will be 
			*						reopened.
			*
			*						The default is FALSE, which means the file shouldn't be reopened.
			*
			*/

			public function getHandler($parameters=NULL){

									//Defined in apf\traits\Type
				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);

				if(!is_null($this->handler)&&!$parameters->find('reopen',FALSE)->toBoolean()->valueOf()){

					return $this->handler;

				}

				$mode			=	$parameters->find('mode','a+',[
																			'r'	=>	['ro','read','readonly'],
																			'r+'	=>	['r+'],
																			'w'	=>	['write'],
																			'w+'	=>	['writeAppend'],
																			'a+'	=>	['append','rw','readwrite'],
																			'x'	=>	['x'],
																			'x+'	=>	['x+'],
																			'c'	=>	['c'],
																			'c+'	=>	['c+']
				]);

				$writeModes	=	['w','a+','w+','a','x','x+','c','c+'];
				$readModes	=	['w+','r','r+','a+'];

				if(in_array($mode,$readModes)&&!ValidateFile::isReadable($this->getRealPath(),$parameters)){

					$user	=	User::getCurrent();
					$msg	=	"User \"$user\" has no read permission on file \"{$this->getRealPath()}\"";
					$msg	=	sprintf('%s Attempted to open file with mode: %s',$msg,$mode);

					throw new FileNotReadableException($msg);

				}

				if(in_array($mode,$writeModes)&&!ValidateFile::isWritable($this->getRealPath(),$parameters)){

					$user	=	User::getCurrent();
					$msg	=	"User \"$user\" has no write permission on file \"$this->getRealPath()\"";
					$msg	=	sprintf('%s Attempted to open file with mode: %s',$msg,$mode);
					throw new FileNotWritableException($msg);

				}

				//Possible thanks to parent::setFileClass('\apf\type\file\Handler');

				$this->handler = parent::openFile($mode);
				$this->handler->parseParameters($this->parameters);

				return $this->handler;

			}

			public function copy($parameters=NULL){

				if(is_string($parameters)){

					$parameters=['dest'=>$parameters];

				}

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				FileUtil::copy($this->getRealPath(),$parameters);

				return new static($parameters->demand('dest'),$parameters);

			}

			public function move($parameters=NULL){

				if(is_string($parameters)){

					$parameters=['dest'=>$parameters];

				}

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				$movedFile	=	FileUtil::move($this->getRealPath(),$parameters);

				return new static($movedFile,$parameters);

			}

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

				return Directory::instance(dirname("$this"));

			}

			/**
			*Method				:	Delete
			*Description		:	Deletes the current file
			*@return	boolean	TRUE	File was deleted
			*@return	boolean	FALSE	File could not be deleted
			*/

			public function delete(){

				return @unlink("$this");

			}

			public function isOwnedBy($user=NULL){

				return $this->getOwner() ==	User::instance($user);

			}

			/**
			*Method				:	isReadable
			*Description		:	Check if the current file is readable
			*@return boolean 	TRUE File exists
			*@return	boolean	FALSE File doesn't exists
			*/

			public function isReadable($parameters=NULL){

				return ValidateFile::isReadable("$this",$parameters);

			}

			/**
			*Method				:	
			*Description		:	Check if the current file is writable
			*
			*/

			public function isWritable($parameters=NULL){

				return ValidateFile::isWritable("$this",$parameters);

			}

			public function isOwnerReadable(){

				return $this->getPerms()->getOwner()->read;

			}

			public function isOwnerWritable(){

				return $this->getPerms()->getOwner()->write;

			}

			public function isOwnerExecutable(){

				return $this->getPerms()->getOwner()->write;

			}

			public function isGroupReadable(){

				return $this->getPerms()->getGroup()->read;

			}

			public function isGroupWritable(){

				return $this->getPerms()->getGroup()->write;

			}

			public function isGroupExecutable(){

				return $this->getPerms()->getGroup()->execute;

			}

			public function isWorldReadable(){

				return $this->getPerms()->getWorld()->read;

			}

			public function isWorldWritable(){

				return $this->getPerms()->getWorld()->write;

			}

			public function isWorldExecutable(){

				return $this->getPerms()->getWorld()->execute;

			}

			public function getGroup(){

				if(!$this->exists(['mustExist'=>TRUE])){

					throw new FileNotFoundException("File \"{$this}\" was not found");

				}

				return Group::instance(parent::getGroup());

			}

			public function getPerms(){

				$this->perms	=	Permission::parse("$this");
				return $this->perms;

			}

			public function getOwner(){

				if(!$this->exists(['mustExist'=>TRUE])){

					throw new FileNotFoundException("File \"{$this}\" was not found");

				}

				return User::instance(parent::getOwner());

			}

		}

	}

