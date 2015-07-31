<?php

	namespace apf\core{

		spl_autoload_register(function($class){

			return AutoLoad::getInstance()->load($class);

		});

		use apf\type\parser\Parameter	as	ParameterParser;
		use apf\core\Log;
		use apf\iface\Log	as	LogInterface;

		class AutoLoad{

			private 	static	$instance	=	NULL;

			private	$config					=	Array(
																	"namespaces"	=>	Array(),
																	"classes"		=>	Array()
			);

			private	$parameters				=	NULL;
			private	$log						=	NULL;

			/**
			*@var Array
			*/
			private	$loadedClasses	=	Array(
			);

			public static function getInstance($parameters=NULL){
			
				if(!is_null(self::$instance)){

					return self::$instance;

				}

				self::$instance					=	new static();
				self::$instance->parameters	=	$parameters;

				return self::$instance;

			}

			public function onNamespaceMatch($nsRegex,Callable $load,Callable $postLoad=NULL){

				return $this->config["namespaces"][$nsRegex]	=	['load'=>$load,'post'=>$postLoad];

			}

			public function onClassMatch($class,Callable $callable){

				return $this->config["class"][$class]	=	$callable;

			}

			public function getConfig(){

				return $this->config;

			}

			public function load($class){

				$require	=	function($file){

					return require $file;

				};

				foreach($this->config["classes"] as $_class){

					if($class==$_class){
						die("yes");
					}

				}

				//Check namespaces
				foreach($this->config["namespaces"]	as $nsRegex=>$args){

					if(preg_match("/$nsRegex/",$class)){

						$file	=	$args["load"]($class);

						if(!file_exists($file)){

							$msg	=	"Unable to load class \"$class\" from file \"$file\" for namespace \"$nsRegex\"";
							throw new \RuntimeException($msg);

						}

						$this->addLoadedClass($class,$file);

						if(!is_null($args["post"])){

							$args["post"]($class,$file);

						}

						$require($file);

					}

				}

				if(is_null($this->parameters)){

					$this->parameters	=	ParameterParser::parse($this->parameters);

					if(is_null($this->log)){

						$log	=	$this->parameters->find('log',NULL);

						if($log instanceof LogInterface){

							$this->log	=	&$log;

						}elseif((boolean)$this->log){

							$this->log	=	Log::getInstance($this->parameters);

						}

					}

				}

			}

			private function log($message,$type,$level){

				if(is_null($this->log)){

					return;

				}

				return $this->log->log($message,['level'=>$level,'type'=>$type,'color'=>'purple']);

			}

			private function addLoadedClass($class,$file,$type="framework"){

				$this->loadedClasses[]	= Array(
															"class"		=>	$class,
															"file"		=>	$file
				);

				$this->log("Load class $class from file $file",'debug',3);

			}

			public function setLog(LogInterface $log){
				$this->log	=	$log;
				return $this;
			}

			public function getLoadedClasses(){

				return $this->loadedClasses;

			}

			public static function getLastLoadedClass(){
			}

		}

	}
