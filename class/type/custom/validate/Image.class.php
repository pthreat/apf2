<?php

	namespace apf\validate{

		use apf\core\File;

		class Image extends Base{

			public static function isImage($file){

				return self::parameterValidation($file);

			}

			public static function getStandardExceptionMessages(){

				return Array(
							Array(
									"value"	=>	-1,
									"msg"		=>	"Image filename is an empty string"
							),
							Array(
									"value"	=>	-2,
									"msg"		=>	'File is not an image'
							)
				);
				
			}

			public static function parameterValidation($file){

				if(!(String::isEmpty($file,$trim=TRUE)===FALSE)){

					return -1;

				}

				return getimagesize(new File($file)) ? TRUE : -2;

			}

			public static function mustBeImage($file,$msg=NULL,$exCode=NULL){

				parent::imperativeValidation(self::isImage($file),$msg,$exCode);

			}

		}

	}

