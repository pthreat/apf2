<?php

	namespace apf\web{

		abstract class Feed{
			
			private	$uri		=	NULL;
			private	$adapter	=	NULL;
			private	$file		=	NULL;

			public function __construct(Array $arguments){

				if(array_key_exists($arguments["uri"])){

					$this->setUri($arguments["uri"]);

				}

				if(array_key_exists($arguments["adapter"])){

					$this->setAdapter($adapter);
					return;

				}

				$this->adapter	=	new \apf\http\adapter\Ecurl();

			}

			public function setUri(\apf\parser\Uri $uri=NULL){
				 
				\apf\Validator::emptyString($uri);

				$this->uri  =   $uri;
				 
			}
			
			public function getUri(){

				return $this->uri;

			}

			public function setAdapter(\apf\Adapter $adapter){

				$this->adapter	=	clone($adapter);

			}

			public function getAdapter($adapter){

				return $this->adapter;

			}

			abstract public function fetch();

		}

	}
