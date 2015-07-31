<?php

	namespace apf\core{

		require "class/core/AutoLoad.class.php";

		use apf\core\OS;
		use apf\core\cache\adapter\File						as	FileCache;
		use apf\type\validate\base\Vector					as	ValidateVector;
		use apf\type\validate\base\Str						as	ValidateString;
		use apf\type\parser\Parameter							as	ParameterParser;

		use apf\iface\Log											as	LogInterface;

		use apf\io\Directory;
		use apf\io\File;
		use apf\io\common\exception\directory\NotFound	as	DirectoryNotFoundException;
		use apf\io\common\exception\directory\Exists		as	DirectoryExistsException;
		use apf\type\util\common\Variable					as	VarUtil;

		//ini_set('scream.enabled',TRUE);

		set_error_handler(function ($errno, $errstr, $errfile, $errline ) {

			if (!(error_reporting() & $errno)) {

				return;

			}

			throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);

		});

		class Kernel{

			/**
			 *@var Kernel $instance
			 *Kernel instance (Singleton pattern)
			 */

			private	static	$instance	=	NULL;

			/**
			*@var ParameterCollection
			*Kernel parameters
			*/

			private	$parameters	=	NULL;

			/**
			 *@var String $apfDir
			 *This variable contains the FULL PATH where the framework resides
			 */

			private $apfDir					=	NULL;

			/**
			 *@var String $appDir
			 *Full path to your project directory
			 *For instance, /home/your_user/your_project/
			 */

			private	static	$appDir		=	NULL;

			/**
			 *@var String $sapi
			 *This variable contains the SAPI being used
			 *It'll be CLI for Command line interface or anything else for web
			 *such as apache2handler in case you're running an Apache web server
			 */

			private	static	$sapi			=	NULL;

			/**
			 *@var apf\core\Log $log
			 *Used for logging kernel messages
			 */

			private	$log;

			/**
			 *@var int $logLevel
			 *Log level: 1 = Normal, 2 = Verbose, 3 = Very verbose
			 */

			private	$logLevel				=	1;

			/**
			 *@var File $logFile
			 *This variable is used to store kernel messages during process stage 0
			 *at this stage the kernel does not know anything about your app configuration.
			 */

			private	$logFile;

			/**
			 *Indicates in which process stage the kernel is at
			 *@var Int $stage
			 */

			private	$stage = 0;

			/**
			 *@var String Framework version
			 */

			const	VERSIONNUM	=	'0.2';

			/**
			 *@var String Framework version string
			 */

			const VERSIONSTR	=	'Veritas';

			private function __construct($parameters=NULL){

				self::$instance	=	$this;

				AutoLoad::getInstance()->onNamespaceMatch('^apf',function($class){

					return self::$instance->loadClass($class);

				});

				$this->parameters	=	ParameterParser::parse($parameters);
				$this->log(sprintf('APF Kernel start; Version %s',self::getFullVersion()),'info',1);

				$os	=	OS::getInstance();
				
				$autoloadedClasses	=	AutoLoad::getInstance()->getLoadedClasses();

				if(sizeof($autoloadedClasses)){

					foreach($autoloadedClasses as $loadedClass){

						$this->log("Loaded class: \"$loadedClass[class]\" from file: \"$loadedClass[file]\"","debug",3);

					}

				}

				$this->log($os->cpuInfo(),'info',1);
				$this->log($os->memInfo(),'info',1);
				$this->log($os->partition('/'),'info',1);

				$config	=	VarUtil::printVar($this->parameters->find('config',$this->getDefaultConfigFile())->valueOf());

				die();

				try{

					$configFile		=	new File($config);
					DI::set('apf',new Config($configFile),$readOnly=TRUE);

					//Once we have the config file, switch kernel to stage 1
					self::setStage(1);

				}catch(\Exception $e){

					throw $e;

				}

				OS::configure();

			}

			public static function getFullVersion(){

				return sprintf('%s; %s',self::VERSIONNUM,self::VERSIONSTR);

			}

			public static function boot($parameters){

				return self::getInstance($parameters);

			}

			private static function getInstance($parameters=NULL){

				if(!is_null(self::$instance)){

					return self::$instance;

				}

				return new static($parameters);

			}

			private function setStage($stage){

				self::$stage = $stage;

			}

			public function getAPFDir(){

				if(is_null($this->apfDir)){

					$this->apfDir	=	substr(dirname(__FILE__),0,strrpos(dirname(__FILE__),'class'));
					$this->apfDir	=	rtrim($this->apfDir,DIRECTORY_SEPARATOR);

				}

				return $this->apfDir;

			}

			private function getDefaultModulesDir(){

				return sprintf('%s%smodules',$this->getDefaultAppDir(),self::$ds);

			}

			public function setLog(LogInterface $log){

				$this->log	=	$log;
				return $this;

			}

			public function log($message,$type,$level){

				static $switchState = 0;

				switch($this->stage){

					case 0:

						if(is_null($this->log)){

							$logParams	=	$this->parameters->findParametersBeginningWith('log');
							$logParams->replace('prepend',sprintf('[Kernel][Stage %s]',$this->stage));
							$this->log	=	Log::getInstance($logParams);

						}

					break;

					case 1:

						if($switchState==FALSE){

							$switchState	=	TRUE;
							$savedLog		=	self::$log->getFile()->getContents();

							$cfg				=	&DI::get('apf')->framework;
							$dir				=	isset($cfg->logs) ? $cfg->logs	:	self::getDefaultLogDir();

							$objDir			=	new Dir();
							$objDir->setName($dir);
							echo $objDir;
							die('abc');

							try{

								$this->log(sprintf('Creating log directory "%s"',$objDir));
								$objDir->create();

							}catch(DirectoryExistsException $e){

								$this->log($e->getMessage(),'info');

							}catch(\Exception $e){

							}

						}

					break;

				}

				$this->log->$type($message,['level'=>$level]);

			}

			//Notify the kernel of a certain event

			public function tell($value,$message){

				return $this->log(sprintf('%s : %s',gettype($value)));

			}

			private function getDefaultLogDir(){

				return sprintf('%s%slog',$this->getDefaultAppDir(),self::$ds);

			}

			private function getDefaultAppDir(){

				$ds	=	DIRECTORY_SEPARATOR;

				return realpath(sprintf('%s%s..%s',$this->getAPFDir(),$ds,$ds));

			}

			private function getDefaultConfigFile(){

				$ds			=	DIRECTORY_SEPARATOR;
				$vsArgs		=	[$this->getDefaultAppDir(),$ds,$ds,$ds];
				return vsprintf('%s%sapp%sconfig%sapf.ini',$vsArgs);

			}

			public function loadClass($class){

				$ds		=	DIRECTORY_SEPARATOR;
				$fwDir	=	static::getInstance()->getAPFDir();

				$class	=	preg_replace(sprintf('/%s%s/','\\','\\'),$ds,$class);

				$class	=	substr($class,strpos($class,$ds)+1);
				$type		=	substr($class,0,strpos($class,'/'));

				$dir		=	'';

				switch($type){

					case 'iface':
						$type		=	'interface';
						$class	=	substr($class,strpos($class,$ds)+1);
					break;

					case 'traits':
						$type		=	'trait';
						$class	=	substr($class,strpos($class,$ds)+1);
					break;

					case 'class':
					default:
						$type	=	'class';
					break;

				}

				//$class	=	ucwords(strtolower(basename($class)));
				$vsArgs	=	[$fwDir,$ds,$type,$ds,$class,$type];
				$path		=	vsprintf("%s%s%s%s%s.%s.php",$vsArgs);

				return $path;

			}

			public static function getSapi(){

				if(is_null(self::$sapi)){

					self::$sapi	=	php_sapi_name();

				}

				return self::$sapi;

			}

			public static function isCli(){

				return self::getSapi()=='cli';

			}

			public static function isWeb(){

				return !self::isCli();

			}

			public function getAppDir(){

				return $this->$appDir;

			}

			/*****************************************************
			 * Logging methods
			 ******************************************************/

			public function error($message,$value=NULL){
			}

			public function warning($message,$value=NULL){
			}

			public function debug($message,$value=NULL){
			}

			public function emergency($message,$value=NULL){
			}

			public function info($message,$value=NULL){
			}

			public function success($message,$value=NULL){
			}

		}

	}

