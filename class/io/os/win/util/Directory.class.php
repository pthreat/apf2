<?php

	namespace apf\io\os\win\util{

		use apf\type\util\common\Variable						as	VarUtil;
		use apf\type\util\base\Str									as	StringUtil;
		use apf\type\parser\Parameter								as	ParameterParser;
		use apf\io\os\win\validate\File							as	ValidateFile;
		use apf\io\commmon\exception\directory\CantCreate	as	CantCreateDirectoryException;
		use apf\iface\io\Directory									as	DirectoryInterface;
		use apf\io\common\util\Directory							as	CommonDirectoryUtil;

		class Directory extends CommonDirectoryUtil{

			public static function create($dir,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$parameters->findInsert('separator',DIRECTORY_SEPARATOR);

				$dir	=	VarUtil::printVar($dir,$parameters);

				if(ValidateFile::exists($dir,$parameters)){

					$msg	=	"Can not create directory \"$dir\", directory already exists";
					throw new CantCreateDirectoryException($msg);

				}

				//If we can't write on the parent, we can't create the directory
				$parent	=	StringUtil::cutFirstToLast($dir,['delimiter'=>DIRECTORY_SEPARATOR]);

				if(!ValidateFile::isWritable($parent,$parameters)){

					$msg	=	"Parent directory of \"$dir\" ($parent) is not writable";
					throw new CantCreateDirectoryException($msg);

				}

				$mode			=	$parameters->find('mode','0700')->toString($parameters)->valueOf();
				$recursive	=	$parameters->find('recursive',TRUE)->toBoolean()->valueOf();

				if(!@mkdir($dir,octdec($mode),$recursive)){

					$msg	=	"Could not create directory \"$dir\" with mode $mode";
					throw new CantCreateDirectoryException($msg);

				}

				return TRUE;

			}

			public static function copy($dir,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

			}

			public static function move($dir,$parameters=NULL){
			}

			public static function rename($dir,$parameters=NULL){
			}

			public static function delete($dir,$parameters=NULL){
			}

		}

	}
