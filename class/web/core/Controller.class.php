<?php

	namespace apf\web\core{

		use apf\core\DI;
		use apf\web\core\Request;

		class Controller{

			protected	$js				=	Array();
			protected	$css				=	Array();
			protected	$raw				=	Array();
			protected	$request			=	NULL;

			public function setRequest(Request $request){

				$this->request	=	$request;

			}

			public function getRequest(){

				return $this->request;

			}

			//Deberiamos obtener los templates dentro del directorio que se llame como el controlador

			/**
			 * Devuelve una vista por defecto que el nombre coincide con controller/accion.tpl
			 * por parametro el archivo deseado
			 *
			 * @example getViewInstance(['directory/foo.tpl'])
			 * @param array $templates
			 * @return View
			 * @throws \Exception
			 */
			public function getViewInstance(Array $templates=Array(),$useActionNameAsTpl=TRUE){

				$controller	=	get_class($this);
				$controller	=	substr($controller,strrpos($controller,"\\")+1);
				$controller	=	strtolower(substr($controller,0,strpos($controller,"Controller")));

				if(empty($templates)&&$useActionNameAsTpl){

					$templates	=	Array(
												sprintf("%s%s%s.tpl",$controller,DIRECTORY_SEPARATOR,$this->request->getAction())
					);

				}


				$view			=	new View($templates);

				//Additional files
				$view->setVar("js",$this->js);
				$view->setVar("css",$this->css);
				$view->setVar("raw",$this->raw);

				foreach(DI::get("config") as $section=>$values){

					if($section=="database"){
						continue;
					}

					$view->setVar($section,$values);

				}

				$view->setVar("req",$this->request);
				//Fix this ... the controller should be accessed from the REQUEST!!!
				$view->setVar("controller",$controller);
				$view->setVar("action",$this->request->getAction());

				return $view;

			}

		}	

	}

