<?php

	namespace apf\core{

		use apf\type\base\IntNum		as	IntType;
		use apf\type\parser\Parameter	as	ParameterParser;

		abstract class Cache{

			use \apf\traits\InnerLog;

			protected	$parameters	=	NULL;
			protected	$source		=	NULL;

			public function __construct($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->findInsert('lifetime',0);
				$this->lifeTime	=	IntType::cast($parameters->find('lifetime')->valueOf());

			}

			abstract public function setSource($source=NULL);
			abstract public function get($name,$getTTL=FALSE);
			abstract public function store($name,$value);
			abstract public function info();
			abstract public function isCached($name=NULL);
			abstract public function listEntries();

			public function getSource(){

				return $this->source;

			}

		}

	}
