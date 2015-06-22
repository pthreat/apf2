<?php

	namespace apf\core\config\adapter{

		use apf\core\Config;
		use apf\parser\file\Ini	as	IniParser;

		class Ini extends Config{

			public function __construct($iniFile=NULL){

				$ini	=	new IniParser($iniFile);
				parent::__construct($ini->parse(['as'=>'array']));

			}

		}

	}
