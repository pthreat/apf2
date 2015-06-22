<?php

	namespace apf\web\core{

		use apf\web\core\Server;
		use apf\util\String		as StringUtil;
		use apf\util\String		as StringValidate;
		use apf\validate\Int		as IntValidate;
		use apf\type\Vector		as Vector;
		use apf\core\DI;

		class Request{

			private	$controller			=	NULL;
			private	$action				=	NULL;
			private	$controllerPath	=	NULL;
			private	$controllerFile	=	NULL;
			private	$debug				=	FALSE;
			private	$log					=	NULL;
			private	$request				=	NULL;
			private	$status				=	200;
			private	$config				=	NULL;

			private	$history				=	Array();

			private	$globals				=	Array(
															"get"		=>NULL,
															"post"	=>NULL,
															"files"	=>NULL
			);

			public function __destruct(){

				if(!$this->isXHR()){

					$this->generateTracking();
					\apf\core\Debug::dumpToFile('/tmp/list',Session::get('__request_history'));

				}

			}

			private function generateTracking(){

				$curPage =	$this->getFullURI();
				$count	=	2;
				$paths	=	Array();

				while($path=$this->getPath($count)){

					$paths[]	=	$path;
					$count++;

				}

				if(sizeof($paths)){

					$curPage	=	sprintf('%s/%s',$curPage,implode('/',$paths));

				}

				try{

					//1 current uri | 0 previous uri | -1 before previous

					$history	=	&Session::get('__request_history');

					if($history[1]!==$curPage){

						$history[-1]	=	$history[0];
						$history[0]		=	$history[1];
						$history[1]		=	$curPage;

					}

				}catch(\Exception $e){

					$history[-1]	=	'/';
					$history[0]		=	'/';
					$history[1]		=	$curPage;

				}

				Session::set('__request_history',$history);

			}

			public function setHistoryIndex($index,$value){

				$history				=	Session::get('__request_history');
				$history[$index]	=	$value;

				Session::set('__request_history',$history);

				return $this;

			}

			public function go($uri){

				Server::redirect($uri);

			}

			//1 current uri | 0 previous uri | -1 before previous
			public function back($num=0){

				try{

					$history	=	Session::get('__request_history');
					$this->go($history[$num]);

				}catch(\Exception $e){

					$this->go('/');

				}

			}

			public function reload(){

				return $this->back(1);

			}

			//Alias of reload
			public function refresh(){

				return $this->go(1);

			}

			public function getHistory($num=NULL){

				try{

					$history	=	Session::get('__request_history');

					if(is_null($num)){

						return $history;

					}

					return $history[(int)$num];

				}catch(\Exception $e){


				}

			}

			public function getCurrentURI(){

				return $this->getHistory(1);

			}

			public function getPreviousURI(){

				return $this->getHistory(0);

			}

			public function getBeforeUri(){

				return $this->getHistory(-1);

			}

			public function getSourceIp(){

				return $_SERVER["REMOTE_ADDR"];

			}

			public function getDomain(){

				return $_SERVER["HTTP_HOST"];

			}

			public function getDomainURI(){

				return sprintf('%s://%s',$this->getProtocol(),$this->getDomain());

			}

			public function getControllerURI($appendAction=NULL){

				if($appendAction){

					return strtolower(sprintf('%s/%s/%s',$this->getDomainURI(),$this->controller,$appendAction));

				}

				return strtolower(sprintf('%s/%s',$this->getDomainURI(),$this->controller));

			}

			public function getFullURI(){

				return $this->getControllerURI($this->action);

			}

			public function getProtocol(){

				$protocol	=	$_SERVER["SERVER_PROTOCOL"];
				$protocol	=	strtolower(trim(substr($protocol,0,strpos($protocol,'/'))));

				return $protocol;

			}

			public function getRedirectUri(){

				$protocol	=	sprintf('%s://',$this->getProtocol());

				$host			=	strtolower($_SERVER["HTTP_HOST"]);
				$redirect	=	trim($_SERVER["REDIRECT_URL"],'/');

				return sprintf('%s%s/%s',$protocol,$host,$redirect);

			}

			public function isPost(){

				return $_SERVER['REQUEST_METHOD'] === 'POST';

			}

			public function isGet(){

				return $_SERVER['REQUEST_METHOD'] === 'POST';

			}

			public function setConfig($config){

				$this->config	=	$config;

			}

			public function __get($var){

				switch(strtolower($var)){

					case 'get':

						if(!is_null($this->globals["get"])){

							return $this->globals["get"];

						}

						$obj	=	new \stdClass();

						if(empty($_GET["request"])){

							return $this->globals["get"]	=	$obj;

						}

						$controller	=	$this->controller;
						$action		=	$this->action;
						$request		=	trim(substr($_GET["request"],strlen($controller)+strlen($action)+1),'/');
						$request		=	"/tok/$request";
						$var			= strtok($request,'/');

						while ($var !== FALSE) {

							$var = strtok('/');

							if(empty($var)){

								break;

							}

							$val = strtok('/');
							$obj->$var	=	$val;

						}

						foreach($_GET as $k=>$v){

							if($k=="request"){
								continue;
							}

							$obj->$k=$v;

						}

						$this->globals["get"]	=	$obj;

						return $obj;
						
					break;

					case 'post':

						if(!is_null($this->globals["post"])){

							return $this->globals["post"];

						}

						$this->globals["post"]			=	Vector::toObject($_POST);

						if(sizeof($_FILES)){

							foreach($_FILES as $key=>$data){

								$this->globals["post"]->$key =	Vector::toObject($data);

							}

						}

						return $this->globals["post"];
						
					break;

				}
			

			}

			public static function create(){

				$obj	=	new static();
				$cfg	=	&DI::get("config")->framework;
				$obj->setConfig($cfg);

				if(empty($_GET["request"])){

					$controller	=	isset($cfg->default_controller)	? $cfg->default_controller : "index";
					$action		=	isset($cfg->default_action) 		? $cfg->default_action : "index";

					$obj->setController($controller);
					$obj->setAction($action);

					return $obj;

				}

				$httpRequest		=	$_GET["request"];

				$obj->setRequest($httpRequest);

				$controller			=	StringUtil::tokenize($httpRequest,'/',0);

				if(empty($controller)){

					$controller	=	"index";

				}

				$action	=	StringUtil::tokenize($httpRequest,'/',1);

				$obj->setController($controller);

				if(empty($action)){

					$action	=	'index';

				}

				$obj->setAction($action);

				return $obj;

			}

			public function setRequest($request=NULL){

				$this->request	=	$request;

			}

			public function getRequest(){

				return $this->request;

			}

			public function setControllerDir($directory=NULL){

				if(!is_dir($directory)){

					throw(new \Exception("Controller directory doesn't exists"));

				}

			}

			public function setController($controller){

				$cfg					=	&DI::get("config")->framework;
				$appName				=	$cfg->appName;
				$ds					=	DIRECTORY_SEPARATOR;
				$cPath				=	sprintf('%s/controller',\apf\core\Kernel::getAppDir());

				$controller			=	ucwords(preg_replace("/\W/",'',$controller));

				$this->setControllerPath($cPath);

				$dir	=	new \DirectoryIterator($cPath);

				foreach($dir as $file){

					$file	=	$file->getFileName();

					if($file=='.'||$file=='..'){
						continue;
					}

					$file	=	substr($file,0,strpos($file,'Controller'));

					if(strtolower($file)==strtolower($controller)){

						$controller	=	$file;
						break;

					}

				}

				$_controller		=	sprintf('%s%s',$controller,'Controller');;

				$ctrl	=	sprintf('%s%s%s.class.php',$cPath,$ds,$_controller);


				if(!file_exists($ctrl)){

					if(empty($cfg->default_controller)){

						$this->status	=	404;
						return;

					}

					$ctrl	=	sprintf('%s%s%s%s.class.php',$cPath,$ds,ucwords($cfg->default_controller),"Controller");


					if(!file_exists($ctrl)){

						$this->status	=	404;
						return;

					}

					$controller	=	sprintf('%s',ucwords($cfg->default_controller));

				}

				$this->controller	=	ucwords($controller);

				return $this;

			}

			public function getController(){

				$cfg				=	&DI::get("config")->framework;
				$appName			=	$cfg->appName;
				return sprintf('\%s\controller\%sController',$appName,$this->controller);

			}

			public function setControllerPath($path){

				$this->controllerPath	=	$path;

			}

			public function getControllerPath(){

				return $this->controllerPath;

			}

			public function setAction($action){

				if(empty($this->controller)){

					throw new \Exception("Can not set action without a controller being set");

				}

				$action				=	preg_replace("/[^a-zA-Z0-9_-]/",'',$action);
				$actionWithSuffix	=	sprintf('%sAction',$action);
				$appName				=	$this->config->appName;
				$controllerClass	=	$this->getController();
				$classMethods		=	get_class_methods($controllerClass);

				foreach($classMethods as $k=>$v){

					$classMethods[$k] = strtolower($v);

				}

				$hasAction			=	in_array(strtolower($actionWithSuffix),$classMethods);
				$hasCall				=	in_array('__call',$classMethods);

				//To enable slugs
				if(empty($action)&&$hasCall){

					$this->action	=	strtolower($this->request);
					return $this;

				}

				if(!$hasAction&&!$hasCall){

					if(empty($cfg->default_action)){

						throw new \Exception("Invalid action $action");

					}

					$this->setAction($cfg->default_action);

					$hasAction	=	in_array(sprintf('%sAction',$cfg->default_action),$classMethods);

					if(!$hasAction){

						throw new \Exception("Requested action doesn't exists. Additionally the default action specified in config doesn't exists");

					}

				}

				$this->action	=	$action;

				return $this;

			}

			public function getAction(){

				return $this->action;

			}

			public function getStatus(){

				return $this->status;

			}

			private function parseRequest(){

			}

			private function _parseRequest(Array $request){

				return \apf\type\Vector::toObject($request);

			}

			private function _parsePathRequest($request){

				if(empty($request["request"])){

					return new \stdClass();

				}

				$request	=	explode('/',$request["request"]);

				$controller	=	$request[0];

				if(isset($request[1])){

					$action		=	$request[1];

				}

				unset($request[0]);
				unset($request[1]);

				$obj	=	new \stdClass();

				for($i=2;isset($request[$i]);$i++){

					if(empty($request[$i])){

						continue;

					}

					if(isset($request[$i+1])){

						$obj->$request[$i]	=	$request[$i+1];
						$i++;

					}else{


						$obj->$request[$i]	=	NULL;

					}

				}

				return $obj;

			}

			public function setLog(\apf\iface\Log $log){
			}

			public function getPath($number){

				IntValidate::mustBeGreaterThan($number,0,'Path number must be a number greater than 0');
				return StringUtil::tokenize($this->request,'/',$number);

			}

			public function find($string=NULL){

				return StringUtil::findTokenizedValue($this->request,'/',$string);

			}

			//Able to tell if its an AJAX request
			public function isXHR(){

				return	!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
							(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

			}

		}

	}

?>
