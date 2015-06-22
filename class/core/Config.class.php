<?php

	namespace apf\core{

		use apf\type\Common				as	Type;
		use apf\type\util\base\Vector	as	VectorUtil;

		class Config{

			private	$data	=	NULL;

			public function __construct(Array $config=NULL){

				$this->data	=	VectorUtil::toStdClass($config);

			}

			public function __get($var){

				if(!isset($this->data->$var)){

					throw new \InvalidArgumentException("Invalid configuration parameter \"$var\"");

				}

				return $this->data->$var;

			}

		}

	}

