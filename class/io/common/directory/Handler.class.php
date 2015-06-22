<?php

	namespace apf\io\common\directory{

		use apf\io\File;
		use apf\io\Directory;
		use apf\io\common\collection\File	as	FileCollection;
		use apf\type\base\Vector				as	VectorType;
		use apf\type\base\Str					as	StringType;
		use apf\type\parser\Parameter			as	ParameterParser;
		use apf\util\convert\meassure\Byte	as	ByteMeassureconverter;

		abstract class Handler extends \DirectoryIterator{

			public function setParameters($parameters=NULL){

				$this->parameters	=	$parameters;

			}

			public function current(){

				$current	=	parent::current();

				$f			=	$this->getPathName();
				$p			=	&$this->parameters;

				$p->replace('file',$f);

				return $current->isDir()	?	Directory::instance($f,$p)	:	new File($p);

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

			public function count(){

				$count	=	0;

				foreach($this as $f){

					$count++;

				}

				return $count;

			}

			public function size($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$sizeIn		=	$parameters->find('in','byte')->valueOf();
				$size			=	\disk_total_space($this->getPath());

				if($sizeIn=='byte'){

					return $size;

				}

				return ByteMeassureconverter::convert($size,['from'=>'byte','to'=>$sizeIn]);

			}

		}

	}
