<?php

	namespace apf\core{

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
			*@var String $apfDir
			*This variable contains the FULL PATH where the framework resides
			*/

			private	static $apfDir		=	NULL;


			/**
			*@var String $appDir
			*Full path to your project directory
			*For instance, /home/your_user/your_project/
			*/

			private	$appDir		=	NULL;


			/**
			*@var String $sapi
			*This variable contains the SAPI being used
			*It'll be CLI for Command line interface or anything else for web
			*such as apache2handler in case you're running an Apache web server
			*/

			private	$sapi			=	NULL;

			/**
			*@var Array
			*/
			private	static $loadedClasses	=	Array();

			/**
			*@var apf\core\Log $log
			*Used for logging kernel messages
			*/

			private	$log;

			/**
			*@var int $logLevel
			*Log level: 1 = Normal, 2 = Verbose, 3 = Very verbose
			*/

			private	$logLevel	=	1;

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

				spl_autoload_register(function($class,$kernel) use ($this){

					$kernel->autoLoad($class);

				});

				$this->parseParameters($parameters);

				$parameters	=	$this->parameters;

				try{

					$this->log(sprintf('APF Kernel start; Version %s',self::getFullVersion()),'info');

				}catch(\Exception $e){

					throw new \RunTimeException($e->getMessage());

				}

				$os	=	OS::getInstance();

				$this->log($os->cpuInfo(),'info');
				$this->log($os->memInfo(),'info');
				$this->log($os->partition('/'),'info');

				$config	=	VarUtil::printVar($parameters->find('config',$this->getDefaultConfigFile())->valueOf());

				echo $config;
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

			public static function boot($parameters=NULL){

				$instance	=	new static($parameters);


			}

			private function setStage($stage){

				self::$stage = $stage;

			}

			public static function getAPFDir(){

				if(is_null(self::$apfDir)){

					self::$apfDir	=	substr(dirname(__FILE__),0,strrpos(dirname(__FILE__),'class'));
					self::$apfDir	=	rtrim(self::$apfDir,DIRECTORY_SEPARATOR);

				}

				return self::$apfDir;

			}

			private function getDefaultModulesDir(){

				return sprintf('%s%smodules',self::getDefaultAppDir(),self::$ds);

			}

			public function setLog(LogInterface $log){

				$this->log	=	$log;
				return $this;

			}

			public static function log($message,$type){

				static $switchState = 0;

				switch($this->stage){

					case 0:

						if(is_null($this->log)){

							$logParams	=	$this->parameters->findParametersBeginningWith('log');
							$this->log	=	Log::getInstance($logParams);
							$this->log->setPrepend(sprintf('[Kernel][Stage %s]',$this->stage));

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

								self::$log(sprintf('Creating log directory "%s"',$objDir));
								$objDir->create();

							}catch(DirectoryExistsException $e){

								self::log($e->getMessage(),'info');

							}catch(\Exception $e){

							}

						}

					break;

				}

				return self::$log->$type($message,$this->parameters);

			}

			//Notify the kernel of a certain event
				
			public function tell($value,$message){

				return self::$log(sprintf('%s : %s',gettype($value)));

			}

			private function getDefaultLogDir(){

				return sprintf('%s%slog',self::getDefaultAppDir(),self::$ds);

			}

			private function setupPHP(){

				if(!isset(DI::get("apf")->framework)){

					throw new \Exception("Invalid configuration file, no [framework] section was found");

				}

				$cfg		=	&DI::get("apf")->framework;

				if(empty($cfg->modules)){

					$cfg->modules	=	self::getDefaultModulesDir();

					if(!is_dir($cfg->modules)){

						throw new DirectoryNotFoundException();

					}

				}

				if(OS::isWindows()&& !empty($cfg->win_locale)){

					$locale	=	$cfg->win_locale;
					$locale	=	empty($locale)	?	'english'	:	$locale;

					if(setlocale(LC_ALL,$locale)===FALSE){

						throw new \Exception("Invalid windows locale specified in configuration");

					}

				}

				if(!Platform::isWindows() && !empty($cfg->locale)) {

					$locale	=	$cfg->locale;
					$locale	=	empty($locale)	?	'en_US.utf8'	:	$locale;

					if (setlocale(LC_ALL,$locale) === FALSE) {

						$msg	=	"Invalid locale specified, it doesn't seems to be installed on your system";
						throw new \Exception($msg);

					}

				}

				if(isset($cfg->dev_mode)&&(int)$cfg->dev_mode>0){

					ini_set("display_errors","On");
					error_reporting(E_ALL);

				}

				//This should be in \apf\web\core\Kernel
				if(!self::isCli()&&isset($cfg->auto_session)){

					session_start();

				}

				if(!self::isCli()&&isset($cfg->headers)){

					header($cfg->headers);

				}

				if(isset($cfg->time_limit)){

					set_time_limit($cfg->time_limit);

				}

				if(isset($cfg->memory_limit)){

					ini_set("memory_limit",$cfg->memory_limit);

				}

				if(isset($cfg->timezone)){

					date_default_timezone_set($cfg->timezone);

				}

			}

			private function getDefaultAppDir(){

				$ds	=	DIRECTORY_SEPARATOR;

				return realpath(sprintf('%s%s..%s',self::getAPFDir(),$ds,$ds));

			}

			private function getDefaultConfigFile(){

				$ds			=	DIRECTORY_SEPARATOR;
				$vsArgs		=	[self::getDefaultAppDir(),$ds,$ds,$ds];
				return vsprintf('%s%sapp%sconfig%sapf.ini',$vsArgs);

			}

			/**
			*Loads a framework class
			*This method is used internally by the framework's autoloader
			*/

			private static function loadAPFClass($class){

				$ds		=	DIRECTORY_SEPARATOR;
				$fwDir	=	self::getAPFDir();

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

				self::$log('debug',"Load: $path",3);

				if(!file_exists($path)){

					throw new \RuntimeException("Class \"$class\" not found in \"$path\"");

				}

				require_once $path;

				self::addLoadedClass($class,$path);

			}

			private static function autoLoad($class){

				if(self::isAPFClass($class)){

					return self::loadAPFClass($class);

				}

				//Load other classes

			}

			private static function addLoadedClass($class,$file,$type="framework"){

				if(!array_key_exists($type,self::$loadedClasses)){

					self::$loadedClasses[$type]		= Array();

				}

				$loadedClasses	=	&self::$loadedClasses[$type];

				self::$loadedClasses[$type][]		= Array(
																		"class"		=>	$class,
																		"file"		=>	$file
				);

			}

			public static function isLoadedClass($class,$type){
			}

			private static function getLoadedClasses(){

				return array_keys(self::$loadedFrameworkClasses);

			}

			private static function isAPFClass($class){

				return strtolower(substr($class,0,strpos($class,'\\'))) == 'apf';

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

