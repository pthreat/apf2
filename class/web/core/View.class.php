<?php

	namespace apf\web\core{

		use \apf\core\DI;
		use \apf\validate\Str		as	ValidateString;
		use \apf\util\Str				as StringUtil;
		use \apf\validate\Int;
		use \apf\validate\Vector;

	   class View{

			private				$_templates		=	Array();
			private				$_vars			=	Array();
			private				$_messages		=	NULL;
			private	static	$minifyStarted	=	FALSE;
			private				$fragmentDebug	=	FALSE;

			public function __construct($templates=NULL){

				if(is_null($templates)){

					return;

				}

				if(!is_array($templates)){

					$this->addTemplate($templates);
					return;

				} 

				if(sizeof($templates)){

					$this->addTemplates($templates);

				}

			}

			public function setFragmentDebug($boolean){

				$this->fragmentDebug	=	(boolean)$boolean;

			}

			public function getFragmentDebug(){

				return $this->fragmentDebug;

			}

			//Print if not empty

			public function printIf($var){

				if(isset($this->_vars[$var])){

					echo $this->_vars[$var];

				}

			}

			public function addTemplate($template){

				if(empty($template)){

					return FALSE;

				}

				$ds	=	DIRECTORY_SEPARATOR;
				$fw	=	&DI::get('config')->framework;
				$path	=	sprintf('%s%s%s%s',Kernel::getAppDir(),$ds,$fw->templates,$ds);
				$tpl	=	sprintf('%s%s%s',$path,$ds,$template);
				$this->_templates[]	=	new \apf\core\File($tpl);

				return TRUE;

			}

			public function addTemplates(Array $templates){

				foreach($templates as $template){

					$this->addTemplate($template);

				}

			}

			public function getTemplates(){

				return $this->_templates;

			}

			public function setVar($name,$value) {

				 $this->$name=$value;
				 return $this;

		   }

			public function setVarArray(Array &$values){

				foreach($values as $name=>$value){

					$this->setVar($name,$value);

				}

		   }

			public function getVars(){

				return $this->_vars;

			}

			public function renderAsString(){

				if(!sizeof($this->_templates)){

					throw(new \Exception("Can't call render(), no templates have been added to this View object"));
					return;

				}

				ob_start();

				foreach($this->_templates as $template){

					require $template;

				}

				$content	=	ob_get_contents();

				ob_end_clean();

				return $content;

			}

			public function setMessages(Array $messages=Array()){

				if(!sizeof($messages)){

					return;

				}

				$requiredKeys	=	Array("msg","status");

				foreach($messages as &$message){

					\apf\Validator::arrayKeys($requiredKeys,$message);

					$this->addMessage($message["msg"],$message["status"]);

				}

			}

			public function addMessage($message,$status="error"){

				switch($status){

					case "success":
					case "error":
					break;

					default:
						throw(new \Exception("Unknown message status \"$status\""));
					break;

				}

				$this->_messages[]	=	Array("msg"=>$message,"status"=>$status);

			}

			public function getMessages(){

				return $this->_messages;

			}

			public function getMessagesAsHtml($template=NULL){

				if(!sizeof($this->_messages)){
					return;
				}

				if(!is_null($template)){

					$view=new View();
					$view->addTemplate($template);
					$view->addVar("messages",$messages);
					return $view->renderAsString();

				}

				$str	=	'<div class="apfw msglist">';

				foreach($this->_messages as $msg){
					$str.='<div class="'.$msg["status"].'">'.$msg["msg"].'</div>';		
				}

				$str	.=	'</div>';

				return $str;

			}

			public function getTemplatesPath(){

				$path	=	trim(DI::get("config")->templates->path);

				if(empty($path)){

					throw new \Exception("Templates path couldn't be found in configuration");

				}

				return $path;

			}

			public function getFragmentsPath(){

				$path	=	trim(DI::get("config")->templates->fragments);

				if(empty($path)){

					throw new \Exception("Fragments path couldn't be found in configuration");

				}

				return $path;

			}

			public function fragmentToggle($condition,Array $tplWhenTrue,Array $tplWhenFalse){

				if($condition){

					$data	=	isset($tplWhenTrue["data"]) ? $tplWhenTrue["data"] : NULL;

					if($data){

						return $this->loadFragment($tplWhenTrue["tpl"],$tplWhenTrue["folder"],$data);

					}

					return $this->loadFragment($tplWhenTrue["tpl"],$tplWhenTrue["folder"]);

				}

				$data	=	isset($tplWhenFalse["data"]) ? $tplWhenFalse["data"] : NULL;

				if($data){

					return $this->loadFragment($tplWhenFalse["tpl"],$tplWhenFalse["folder"],$data);

				}

				return $this->loadFragment($tplWhenFalse["tpl"],$tplWhenFalse["folder"]);

			}

			//Arg1: fragment name, arg2: fragment folder, arg3: data, arg4: exit on no data

			public function loadFragment($name=NULL,$folder=NULL){

				$args	=	func_get_args();

				$data	=	NULL;

				if(array_key_exists(2,$args)){

					$data	=	$args[2];

					if(empty($data)){

						return FALSE;

					}

				}

				ValidateString::mustBeNotEmpty($name,"Fragment name must be a non empty string");

				if(!ValidateString::isString($folder)){

					$folder	=	$this->_controller;

				}

				$name	=	trim($name);
				$fw	=	DI::get('config')->framework;

				if(!isset($fw->fragments)||empty($fw->fragments)){

					throw new \Exception("Don't know how to load fragments, no path was specified in configuration");
				}

				$ds			=	DIRECTORY_SEPARATOR;
				$fragment	=	sprintf('%s%s%s%s',Kernel::getAppDir(),$ds,$fw->fragments,$ds);
				$fragment	=	sprintf('%s%s%s%s',$fragment,$folder,$ds,$name);
				$fragment	=	new \apf\core\File($fragment);

				$this->assignConfigVars();

				if($this->fragmentDebug){

					echo "<div class=\"__apf_fragment_debug\">";

				}

				require $fragment;

				if($this->fragmentDebug){

					echo "</div>";

				}

				return TRUE;

			}

			public function loadFragmentAsString($name=NULL,$folder=NULL,$data=NULL){

				$args	=	func_get_args();

				$data	=	NULL;

				if(array_key_exists(2,$args)){

					$data	=	$args[2];

					if(empty($data)){

						return FALSE;

					}

				}

				ValidateString::mustBeNotEmpty($name,"Fragment name must be a non empty string");

				if(!ValidateString::isString($folder)){

					$folder	=	$this->_controller;

				}

				$name	=	trim($name);
				$fw	=	DI::get('config')->framework;

				if(!isset($fw->fragments)||empty($fw->fragments)){

					throw new \Exception("Don't know how to load fragments, no path was specified in configuration");
				}

				$ds			=	DIRECTORY_SEPARATOR;
				$fragment	=	sprintf('%s%s%s%s',Kernel::getAppDir(),$ds,$fw->fragments,$ds);
				$fragment	=	sprintf('%s%s%s%s',$fragment,$folder,$ds,$name);
				$fragment	=	new \apf\core\File($fragment);

				$this->assignConfigVars();

				ob_start();

				if($this->fragmentDebug){

					echo "<div class=\"__apf_fragment_debug\">";

				}

				require $fragment;

				if($this->fragmentDebug){

					echo "</div>";

				}

				$content	=	ob_get_contents();

				ob_end_clean();

				return $content;

			}

			public function iterateFragment($frag,$folder,$times=0,$data=NULL){

				Int::mustBePositive($times,"Amount of iterations must be a number greater than 0");

				for($i=0;$i<$times;$i++){

					$this->loadFragment($frag,$folder,$data);

				}

			}

			public function iterateFragmentData($frag,$folder,$data){

				Vector::mustBeArray($data);

				if(!sizeof($data)){

					return FALSE;

				}

				foreach($data as $k=>$d){

					$this->setVar('__iterationNumber',$k);
					$this->loadFragment($frag,$folder,$d);

				}

				return TRUE;

			}

			public function render(){

				if(!sizeof($this->_templates)){

					throw(new \Exception("Can't call render(), no templates have been added to this View object"));
					return;

				}

				if(DI::get('config')->framework->minifyOutput&&!self::$minifyStarted){

					self::$minifyStarted=1;
					ob_start('\apf\util\Str::minify');

				}

				foreach($this->_templates as $template){

					require $template;

				}


			}

			private function assignConfigVars(){

				foreach(DI::get("config") as $k=>$cfg){

					if($k=="database"){

						continue;

					}

					$this->$k=$cfg;

				}

			}

			public function resetTemplates(){

				$this->_templates	=	Array();

			}

			public function __toString(){

				$str	=	NULL;

				if(!sizeof($this->_templates)){

					return "";

				}

				foreach($this->_templates as $template){

					$str	.= file_get_contents($template);

				}

				return $str;

			}

		}
 
	}

?>
