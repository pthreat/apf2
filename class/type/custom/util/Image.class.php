<?php

	namespace apf\util{

		use apf\core\File;
		use apf\validate\Image	as	ValidateImage;
		use apf\validate\Str		as	ValidateString;
		use apf\validate\Int		as	IntValidate;

		class Image{

			public static function base64ToFile($base64,$save){

				$base64	=	substr($base64,strpos($base64,',')+1);
				$base64	=	base64_decode($base64);

				if(!$base64){

					throw new \Exception("Invalid base64 image string");

				}

				$file		=	new File();
				$file->setFileName($save);
				$file->setContents($base64);
				$file->write();

				ImageValidate::mustBeImage($file);

				return $file;

			}

			public static function resize($image,$destination,$width,$height,$filter=NULL){

				ValidateImage::mustBeImage($image);
				ValidateString::mustBeNotEmpty($destination,"Destination must not be empty");

				$width	=	(int)$width;
				$height	=	(int)$height;

				IntValidate::mustBePositive($width,"Image width must be a positive number");
				IntValidate::mustBePositive($height,"Image height must be a positive number");

				$filter	=	empty($filter)	?	\Imagick::FILTER_LANCZOS	:	$filter;
				$destDir	=	dirname($destination);

				if(!is_dir($destDir)){

					if(!@mkdir($destDir,$mode=0777,$recursive=TRUE)){

							throw new \Exception("Could not create directory $destDir to save resized image");	
					}

				}

				$resize	=	new \Imagick();
				$resize->readImage($image);
				$resize->resizeImage($width,$height,$filter,1,$bestFit=TRUE);
				$resize->writeImage($destination);
				$resize->clear();
				$resize->destroy();

			}

		}

	}

?>
