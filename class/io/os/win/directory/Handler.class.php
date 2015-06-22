<?php

	namespace apf\io\os\win\directory{

		use apf\io\os\win\File;
		use apf\io\os\win\Directory;
		use apf\io\common\collection\File	as	FileCollection;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\base\Str					as	StringType;

		class Handler extends \DirectoryIterator{

			public function setParameters($parameters=NULL){

				$this->parameters	=	$parameters;

			}

			public function current(){

				$current	=	parent::current();

				$f			=	$this->getPathName();
				$p			=	&$this->parameters;

				return $current->isDir()	?	DirectoryType::cast($f,$p)	:	FileType::cast($f,$p);

			}

			public function toArray(){

				$fileCollection	=	new FileCollection();

				foreach ($this as $file){

					$fileCollection->add($file);

				}

				return $fileCollection;

			}

			public function toString(){

				return StringType::cast(sprintf('%s',$this));

			}

		}

	}
