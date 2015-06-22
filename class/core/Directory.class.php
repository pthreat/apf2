<?php

	namespace apf\core{

		use apf\exception\directory\Exists;
		use apf\exception\directory\NotFound;

		class Directory{

			private	$directory	=	NULL;

			public function __construct($directory=NULL){

				if(!is_null($directory)){

					$this->setName($directory);

				}

			}

			public function exists(){

				return is_dir($this->directory);

			}

			public function create($perms=0770){

				if($this->exists()){

					throw new \DirectoryExistsException($this->directory);

				}

				if(!@mkdir($this->directory,$perms,$recursive=TRUE)){

					throw new \Exception(sprintf('Could not create directory %s',$this->directory));

				}

			}

			public function setName($directory){
		
				if(!is_dir($directory)){

					throw(new \Exception("Invalid directory \"$directory\". Directory doesn't exists"));

				}

				$this->directory	=	$directory;

			}

			public function getDirectory(){

				return $this->directory;

			}

			public function getFilesAsArray(){

				$directoryIterator	=	new \DirectoryIterator($this->directory);

				$files	=	Array();

				foreach ($directoryIterator as $fileInfo){

					if($fileInfo->isDot()){
						continue;
					}

					$files[]	=	$fileInfo->getFileName();

				}

				return $files;

			}

			public function __toString(){

				return realpath($this->directory);

			}	

		}

	}
?>
