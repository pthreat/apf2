<?php

	namespace apf\io\os\win\util{

		use apf\type\base\Str									as	StringType;
		use apf\type\base\IntNum								as	IntType;
		use apf\type\base\Vector								as	VectorType;
		use apf\type\parser\Parameter							as	ParameterParser;
		use apf\type\util\common\Variable					as	VarUtil;
		use apf\type\util\base\Str								as	StringUtil;

		use apf\io\os\win\parser\Permission				as	PermissionParser;

		use apf\acl\os\win\User;
		use apf\acl\os\win\Group;

		use apf\io\os\win\util\Directory					as	DirUtil;

		use apf\io\os\win\validate\File						as	ValidateFile;
		use apf\io\os\win\validate\Directory				as	ValidateDir;

		use apf\io\common\exception\file\NotFound			as	FileNotFoundException;
		use apf\io\common\exception\file\NotWritable		as	FileNotWritableException;
		use apf\io\common\exception\file\NotReadable		as	FileNotReadableException;
		use apf\io\common\exception\file\CouldNotCopy	as	CouldNotCopyException;
		use apf\io\common\exception\file\CouldNotMove	as	CouldNotMoveException;

		use apf\io\common\util\File							as	CommonFileUtil;

		class File extends CommonFileUtil{

			public static function copy($file,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$dest			=	$parameters->demand('dest')->toString()->valueOf();
				$operation	=	$parameters->find('operation','copy')->toString()->valueOf();
				$operation	=	$operation=='copy'	?	'copy'	:	'rename';

				$destIsDir	=	is_dir($dest);
				clearstatcache($file,TRUE);

				$destDir		=	$destIsDir	?	$dest	:	dirname($dest);

				//if the target directory does not exists, attempt to create it
				if(!ValidateFile::exists($destDir)){

					DirUtil::create($destDir);

				}

				$file			=	VarUtil::printVar($file);
				$ds			=	DIRECTORY_SEPARATOR;
				$dest			=	$destIsDir	?	sprintf('%s%s%s',$dest,$ds,basename($file))	:	$dest;

				if(!ValidateFile::isReadable($destDir)){

					$group	=	self::getGroup($dest);
					$owner	=	self::getOwner($dest);
					$curUser	=	SysUserType::getCurrent();

					$msg		=	"Can't $operation file \"$file\" to \"$destDir\". ";
					$msg		=	sprintf('%s, directory "%s" is not readable by you',$msg,$destDir);
					$msg		=	sprintf("(%s) %s. Owned by $owner, Group $group",$curUser,$msg);

					throw new FileNotReadableException($msg);

				}

				if(!ValidateFile::isWritable($destDir)){

					$group	=	self::getGroup($destDir);
					$owner	=	self::getOwner($destDir);
					$curUser	=	SysUserType::getCurrent();

					$msg		=	"Can't $operation file \"$file\" to \"$destDir\". ";
					$msg		=	sprintf('%s, directory "%s" is not writable by you',$msg,$destDir);
					$msg		=	sprintf("(%s) %s. Owned by $owner, Group $group",$curUser,$msg);

					throw new FileNotWritableException($msg);

				}

				$overwrite	=	$parameters->find('overwrite',FALSE)->toBoolean()->valueOf();

				if(ValidateFile::exists($dest)&&!$overwrite){

					$msg	=	"File already exists, will not overwrite the file \"$dest\" unless you ";
					$msg	=	sprintf('%s set the overwrite parameter to TRUE',$msg);

					throw new FileNotWritableException($msg);

				}

				if(!call_user_func_array($operation,[$file,$dest])){

					$msg	=	"Unexpected error! Could not $operation file \"$file\" to \"$dest\"";
					throw new CouldNotCopyException($msg);

				}

				return $dest;

			}

			public static function move($file,$parameters=NULL){

				self::copy($file,$parameters);

			}

			public static function getOwner($file,$parameters=NULL){

				ValidateFile::mustExist($file,"Can not get owner, file %s doesn't exists");

				$owner	=	User::instance(fileowner($file),$parameters);

				clearstatcache($file,TRUE);

				return $owner;

			}

			public static function getGroup($file,$parameters=NULL){

				ValidateFile::mustExist($file,"Can not get owner, file %s doesn't exists");

				$group	=	Group::instance(filegroup($file),$parameters);
				clearstatcache($file,TRUE);

				return $group;

			}

			public static function getPerms($file,$parameters=NULL){

				$file	=	VarUtil::printVar($file);

				ValidateFile::mustExist($file,"Can not get owner, file %s doesn't exists");

				clearstatcache($file,TRUE);

				return PermissionParser::fromFile($file,$parameters);

			}

			public static function chmod($file,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$mode			=	PermissionParser::parse($file,$parameters->demand('perms'))->getValue();

				if(!ValidateFile::isWritable($file,$parameters)){

					$group	=	self::getGroup($file);
					$owner	=	self::getOwner($file);
					$msg		=	"File is not writable. Owned by $owner, Group $group";

					throw new FileNotWritableException($msg);

				}

				chmod(VarUtil::printVar($file),octdec($mode));

				clearstatcache($file,TRUE);

			}

		}

	}
