<?php

	namespace apf\core\os{

		use apf\core\Alias;
		use apf\type\util\base\Str	as StringUtil;
		use apf\iface\OS				as	OSInterface;

		abstract class Common implements OSInterface{

			//Uses the singleton trait method self::getInstance comes straight from this trait
			use \apf\traits\pattern\Singleton;

			/**
			*@var String $host
			*This variable contains the current host name
			*/

			private	$host		=	NULL;

			/**
			*@var String $release
			*This variable contains the current operating system release
			*/

			private	$release	=	NULL;

			/**
			*@var String $name
			*This variable contains the current operating system name
			*i.e: FreeBSD, Linux, Windows, Darwin, etc
			*/

			private	$name		=	NULL;

			/**
			*@var String $version
			*This variable contains the current operating system's version
			*/

			private	$version	=	NULL;

			public function is($name){

				return $this->getGenericName()==$name;

			}

			public function isMac(){

				return $this->is('darwin');

			}

			public function isWindows(){

				return $this->is('windows');

			}

			public function isLinux(){

				return $this->is('linux');

			}

			public function isFreeBSD(){

				return $this->is('freebsd');

			}

			public function isOfUnixFamily(){

				return $this->isFreeBSD()||$this->isLinux()||$this->isMac();

			}

			public function getHostName(){

				if(is_null($this->host)){

					$this->host	=	php_uname('n');

				}

				return $this->host;

			}

			public function getRelease(){

				if(is_null($this->release)){

					$this->release	=	php_uname('r');

				}

				return $this->release;

			}

			public function getName(){

				if(is_null($this->name)){

					$this->name	=	php_uname('s');

				}

				return $this->name;

			}

			public function getGenericName(){

				$name		=	$this->getName();
				$value	=	strtolower(substr($name,0,strpos($name,' ')));
				return $value;

			}

			public function getFamily(){

				return $this->getGenericName()	==	'windows'	?	'win'	:	'unix';

			}

			public function getVersion(){

				if(is_null($this->version)){

					$this->version	=	php_uname('v');

				}

				return $this->version;

			}

		}

	}
