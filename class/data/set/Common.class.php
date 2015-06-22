<?php

	namespace apf\data\set{

		use apf\io\Directory;
		use apf\io\File;
		use apf\type\base\Vector									as	VectorType;
		use apf\type\collection\common\UniqueIndex			as	UniqueIndexCollection;
		use apf\type\util\common\Variable						as	VarUtil;
		use apf\exception\type\dataset\MustDefineDirectory	as	MustDefineDatasetDirectory;

		abstract class Common{

			//Datasets base directory
			protected static $dataSetDir	=	NULL;
			protected static $sets			=	NULL;

			//This method must be called before trying to load a data set
			public static function setDatasetDir($dir){

				self::$dataSetDir	=	Directory::instance($dir,['mustExist'=>TRUE]);

			}

			public static function fetch($set,$name){

				if(is_null(self::$dataSetDir)){

					$msg	=	'You must define a data set directory before fetching a data set';
					throw new MustDefineDatasetDirectory($msg);

				}

				if(is_null(self::$sets)){

					self::$sets	=	UniqueIndexCollection::instance();

				}
	
				$ds	=	DIRECTORY_SEPARATOR;
				$set	=	VarUtil::printVar($set);
				$name	=	VarUtil::printVar($name);
				$set	=	sprintf('%s.json',$set);
				$hash	=	sha1(sprintf('%s%s%s%s%s',self::$dataSetDir,$ds,$name,$ds,$set));

				if(self::$sets->hasKey($hash)){

					return VectorType::cast(json_decode(self::$sets[$hash],$assoc=TRUE));

				}

				try{

					$file	=	self::$dataSetDir
					->find($name)
					->find($set);

					self::$sets->add($file->getContents(),['key'=>$hash]);

					return VectorType::cast(json_decode(self::$sets[$hash],$assoc=TRUE));

				}catch(FileNotFoundException $e){

					throw new DataSetNotFoundException("Could not find data set $name/$set");

				}catch(FileNotReadableException $e){

					$msg	=	"Could not read data set $name/$set, check permissions and try again!";
					throw new DataSetNotReadableException($msg);

				}

			}
			
		}

	}
