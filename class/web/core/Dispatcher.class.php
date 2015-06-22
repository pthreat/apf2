<?php

	namespace apf\web\core{

		use apf\core\DI;

		class Dispatcher{

			private	$debug		=	FALSE;
			private	$log			=	NULL;
			private	$request		=	NULL;
			private	$config		=	NULL;

			public function __construct($config=NULL){

				if(is_null($config)){

					$this->config	=	&DI::get("config")->framework;

				}

				$this->request	=	Request::create($this->config);

			}

			public function getRequest(){

				return $this->request;

			}

			public function setLog(\apf\iface\Log $log){

				$this->log	=	$log;

			}

			public function setDebug($bool=TRUE){

				$this->debug	=	$bool;

			}
	
			public function getDebug(){

				return $this->debug;

			}

			public function dispatch(){

				if($this->request->getStatus()!==200){

					throw new \Exception("Request object has returned status %d",$this->request->getStatus());
				}

				$controller			=	$this->request->getController();
				$action				=	sprintf('%sAction',$this->request->getAction());
				$objController		=	new $controller;
				$objController->setRequest($this->request);

				return $objController->$action($this->request);

			}

		}

	}

