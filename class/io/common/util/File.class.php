<?php

	namespace apf\io\common\util{

		abstract class File{

			public static function md5($file,$parameters=NULL){

				$file			=	VarUtil::printVar($file,$parameters);

				ValidateFile::mustExist($file,"Cant get md5 hash for file \"%s\", file doesn't exists");

				if(!ValidateFile::isReadable($file)){

					throw new FileNotReadableException("Can not md5 hash \"$file\", file is not readable");

				}

				$parameters	=	ParameterType::parse($parameters);

				return StringType::cast(md5_file($file,$parameters->find('raw',FALSE)->getValue()));

			}

			public static function sha1($file,$parameters=NULL){

				$file			=	VarUtil::printVar($file,$parameters);

				ValidateFile::mustExist($file,"Cant get sha1 hash for file \"%s\", file doesn't exists");

				if(!ValidateFile::isReadable($file)){

					throw new FileNotReadableException("Can not sha1 hash \"$file\", file is not readable");

				}

				$parameters	=	ParameterType::parse($parameters);

				return StringType::cast(sha1_file($file,$parameters->find('raw',FALSE)->getValue()));

			}

		}

	}
