<?php

	namespace apf\io\os\unix\validate{

		use apf\type\base\Str							as	StringType;
		use apf\type\util\common\Variable			as	VarUtil;
		use apf\type\parser\Parameter					as	ParameterParser;

		use apf\io\os\unix\util\File					as	FileUtil;
		use apf\io\os\unix\parser\Permission		as	PermissionParser;

		use apf\acl\os\unix\User;
		use apf\acl\os\unix\Group;

		use apf\io\common\validate\File					as	CommonFileValidation;
		use apf\io\common\exception\file\NotFound		as	FileNotFoundException;
		use apf\io\common\exception\file\NotReadable	as	FileNotReadableException;
		use apf\io\common\exception\file\NotWritable	as	FileNotWritableException;

		class File extends CommonFileValidation{

			public static function isReadable($file,$parameters=NULL){

				$file			=	VarUtil::printVar($file);

				$isReadable	=	is_readable($file);	
				clearstatcache(TRUE,$file);

				$parameters	=	ParameterParser::parse($parameters);

				//The user can ask BY WHO the file is writable or not
				//Default assumed is the user executing this process.

				$by			=	$parameters->find('by',FALSE)->toBoolean()->valueOf();
				$by			=	!$by	?	User::getCurrent()	:	User::instance($by);

				//If the file is not writable BUT is owned by the user running this process
				//then fix the file permissions

				if($parameters->find('fixPerms',TRUE)->valueOf()){

					if(!$isReadable&&$by==FileUtil::getOwner($file,$parameters)){

						//r== means, assign readable and == means keep the other 
						//permissions on the file INTACT.

						$perms	=	PermissionParser::parse($file,'r==')->getValue();

						if(!chmod($file,octdec($perms))){

							$msg	=	"Could not auto fix readable permissions on file \"$file\"";
							throw new FileNotWritableException($msg);

						}

						clearstatcache(TRUE,$file);

						return TRUE;

					}

				}

				return $isReadable;

			}

			public static function isWritable($file,$parameters=NULL){

				$file			=	VarUtil::printVar($file);
				$parameters	=	ParameterParser::parse($parameters);

				//The user can ask BY WHO the file is writable or not
				//Default assumed is the user executing this process.

				$by			=	$parameters->find('by',FALSE)->valueOf();
				$by			=	$by	?	User::getCurrent()	:	User::instance($by);

				$isWritable	=	is_writable($file);

				clearstatcache(TRUE,$file);

				//If the file is not writable BUT is owned by the user running this process
				//then fix the file permissions

				if($parameters->find('fixPerms',TRUE)->getValue()){

					if(!$isWritable&&$by==FileUtil::getOwner($file,$parameters)){

						$perms	=	PermissionParser::parse($file,'=w=')->getValue();

						if(!chmod($file,octdec($perms))){

							$msg	=	"Could not auto fix writable permissions on file \"$file\"";
							throw new FileNotWritableException($msg);

						}

						clearstatcache(TRUE,$file);

						return TRUE;

					}

				}

				return $isWritable;

			}

			public static function exists($file,$parameters=NULL){

				$file 	=	VarUtil::printVar($file,$parameters);
				$exists	=	file_exists($file);

				clearstatcache($file,TRUE);

				return $exists;

			}

		}

	}
