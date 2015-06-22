<?php

	namespace apf\core\cache\adapter\file{

		use apf\io\File;
		use apf\core\cache\Entry	as	CacheEntry;
	
		class Entry extends CacheEntry{

			public function __construct($parameters=NULL){

				parent::__construct($parameters);
				$file	=	new File($this->makeName());
				parent::setSize($file->getSize());

			}

			private function makeName(){

				return sprintf('%s/%s',$this->source,$this->name);

			}

			public function delete(){

				return $this->getFile()->delete();

			}

			public function getFile(){

				return new File($this->makeName());

			}

		}

	}
