<?php

	namespace apf\io\common\validate{

		use apf\iface\io\validate\File					as	FileValidationInterface;
		use apf\type\util\common\Variable				as	VarUtil;
		use apf\type\parser\Parameter						as	ParameterParser;

		use apf\io\common\exception\file\NotFound		as	FileNotFoundException;
		use apf\io\common\exception\file\NotReadable	as	FileNotReadableException;
		use apf\io\common\exception\file\NotWritable	as	FileNotWritableException;

		abstract class File implements FileValidationInterface{

			public static function mustExist($file,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				if(static::exists($file,$parameters)){

					return TRUE;

				}

				$msg	=	VarUtil::printVar($parameters->find('msg',"File \"$file\" doesn't exists")->valueOf());
				$code	=	(int)$parameters->find('code',0)->valueOf();

				throw new FileNotFoundException($msg,$code);

			}

			public static function mustBeWritable($file,$parameters=NULL){

				if(!static::isWritable($file,$parameters)){

					throw new FileNotWritableException("File $file is not writable");

				}

			}

			public static function mustBeReadable($file,$parameters=NULL){

				if(!static::isReadable($file)){

					throw new FileNotReadableException("File $file is not readable");

				}

			}

		}

	}
