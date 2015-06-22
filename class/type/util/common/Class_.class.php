<?php

		namespace apf\type\util\common{

			use apf\type\base\Str	as	StringType;

			class Class_{

				public static function hasMethod($class,$method,$filters=NULL,$mode="all"){

					$validModes	=	Array('all','any','some');
				
					$rc	=	new \ReflectionClass($class);

					//If no filters where specified, then check if the given class has a given method
					//and thats it! We don't need further checking if the user hasn't specified any filters.
					if(is_null($filters)){

						return $rc->hasMethod($method);

					}

				}
				
				public static function removeNamespace($class){
						
					$rc	=	new \ReflectionClass($class);
					return StringType::cast($rc->getShortName());

				}

				public static function getNamespace($class){
						
					\apf\validate\Class_::mustExist($class,"Class $class doesn't exists");

					$rc	=	new \ReflectionClass($class);
					return StringType::cast($rc->getNamespaceName());

				}

				public static function getPublicMethods($class,$noMagic=TRUE){

					\apf\validate\Class_::mustExist($class,"Class $class doesn't exists");

					$rc		=	new \ReflectionClass($class);
					$methods	=	$rc->getMethods(\ReflectionMethod::IS_PUBLIC);
					$data		=	Array();

					$magic	=	Array(
												"__construct",
												"__wakeup",
												"__call",
												"__invoke",
												"__sleep",
												"__destruct",
												"__get",
												"__set",
												"__serialize",
												"__toString",
												"__set_state",
												"__clone",
												"__debugInfo",
												"__callStatic",
												"__isset"
					);

					foreach($methods as $m){

						if($noMagic && in_array($m,$magic)){

							continue;

						}

						$data[]	=	$m->getName();

					}

					return $data;

				}

			}

		}

