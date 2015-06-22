<?php

	namespace apf\core{

		use apf\core\OS;
		use apf\core\cache\adapter\File						as	FileCache;
		use apf\type\validate\base\Vector					as	ValidateVector;
		use apf\type\validate\base\Str						as	ValidateString;
		use apf\type\parser\Parameter							as	ParameterParser;

		use apf\io\Directory;
		use apf\io\File;
		use apf\io\common\exception\directory\NotFound	as	DirectoryNotFoundException;
		use apf\io\common\exception\directory\Exists		as	DirectoryExistsException;

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

			private static $apfDir		=	NULL;


			/**
			*@var String $appDir
			*Full path to your project directory
			*For instance, /home/your_user/your_project/
			*/

			private static $appDir		=	NULL;


			/**
			*@var String $sapi
			*This variable contains the SAPI being used
			*It'll be CLI for Command line interface or anything else for web
			*such as apache2handler in case you're running an Apache web server
			*/

			private static	$sapi			=	NULL;

			/**
			*@var String $dSeparator
			*This variable contains the directory separator
			*it may vary between operating systems, ie: Windows / Linux
			*/

			protected static $ds			=	'/';

			/**
			*@var String $nsSeparator
			*This variable is provided for code clarity only
			*/

			protected static $nss		=	'\\';

			/**
			*@var Array
			*/

			private static $loadedClasses	=	Array();

			/**
			*@var apf\core\Log $log
			*Used for logging kernel messages
			*/

			private static $log;

			/**
			*@var int $logLevel
			*Log level: 1 = Normal, 2 = Verbose, 3 = Very verbose
			*/

			private static $logLevel	=	1;

			/**
			*@var File $logFile
			*This variable is used to store kernel messages during process stage 0
			*at this stage the kernel does not know anything about your app configuration.
			*/

			private static $logFile;

			/**
			*Indicates in which process stage the kernel is at
			*@var Int $stage
			*/

			private static $stage = 0;

			/**
			*@var String Framework version
			*/

			const	VERSIONNUM	=	'0.2';

			/**
			*@var String Framework version string
			*/

			const VERSIONSTR	=	'Veritas';

			public static function getFullVersion(){

				return sprintf('%s; %s',self::VERSIONNUM,self::VERSIONSTR);

			}

			public static function boot($parameters=NULL){

				spl_autoload_register(sprintf('%s\Kernel::autoLoad',__NAMESPACE__));

				self::$parameters	=	ParameterParser::parse($parameters);

				self::log(sprintf('APF Kernel start; Version %s',self::getFullVersion()),'info');

				self::log(OS::getInstance()->cpuInfo(),'info');
				self::log(OS::getInstance()->memInfo(),'info');
				self::log(OS::getInstance()->partition('/'),'info');

				$parameters	=	ParameterParser::parse($parameters);

				$config		=	VarUtil::printVar($parameters->find('config',self::getDefaultConfigFile())->valueOf());

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

			private static function setStage($stage){

				self::$stage = $stage;

			}

			public static function getAPFDir(){

				if(is_null(self::$apfDir)){

					self::$apfDir	=	substr(dirname(__FILE__),0,strrpos(dirname(__FILE__),'class'));
					self::$apfDir	=	rtrim(self::$apfDir,self::$ds);

				}

				return self::$apfDir;

			}

			private static function getDefaultModulesDir(){

				return sprintf('%s%smodules',self::getDefaultAppDir(),self::$ds);

			}

			private static function log($message,$type){

				static $switchState = 0;

				switch(self::$stage){

					case 0:

						if(is_null(self::$logFile)){

							self::$logFile = new File(['tmp'=>TRUE]);

						}

						if(is_null(self::$log)){
			
							self::$log	=	new Log(self::$logFile);
							self::$log->useLogDate();
							self::$log->setEcho(FALSE);
							self::$log->setPrepend(sprintf('[Kernel][Stage %s]',self::$stage));

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

				return self::$log->$type($message);

			}

			//Notify the kernel of a certain event
				
			public static function tell($value,$message){

				return self::log(sprintf('%s : %s',gettype($value)));

			}

			private function getDefaultLogDir(){

				return sprintf('%s%slog',self::getDefaultAppDir(),self::$ds);

			}

			private static function setupPHP(){

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

			private static function getDefaultAppDir(){

				return realpath(sprintf('%s%s..%s',self::getAPFDir(),self::$ds,self::$ds));

			}

			private static function getDefaultConfigFile(){

				$vsArgs		=	[self::getDefaultAppDir(),self::$ds,self::$ds,self::$ds];
				return vsprintf('%s%sapp%sconfig%sapf.ini',$vsArgs);

			}

			/**
			*Loads a framework class
			*This method is used internally by the framework's autoloader
			*/

			private static function loadAPFClass($class){

				$ds		=	self::getDs();
				$fwDir	=	self::getAPFDir();

				$class	=	preg_replace(sprintf('/%s%s/',self::$nss,self::$nss),$ds,$class);

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

				$vsArgs	=	[$fwDir,$ds,$type,$ds,$class,$type];
				$class	=	ucwords(basename($class));
				$path		=	vsprintf("%s%s%s%s%s.%s.php",$vsArgs);

				self::addLoadedClass($class,$path);

				require $path;

			}

			public static function autoLoad($class){

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

				self::$log->debug("Load class \"$class\" from file \"$file\"");

			}

			public static function isLoadedClass($class,$type){
			}

			private static function getLoadedClasses(){

				return array_keys(self::$loadedFrameworkClasses);

			}

			private static function isAPFClass($class){

				return strtolower(substr($class,0,strpos($class,self::$nss))) == 'apf';

			}

			public static function getDS(){

				if(is_null(self::$ds)){

					self::$ds = self::isWindows() ? '\\' : '/';

				}

				return self::$ds;

			}

			//Alias of getDs
			public function getDirectorySeparator(){

				return self::$ds;

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

			public static function getAppDir(){

				return self::$appDir;

			}

			/*****************************************************
			* Logging methods
			******************************************************/

			public static function error($message,$value=NULL){
			}

			public static function warning($message,$value=NULL){
			}

			public static function debug($message,$value=NULL){
			}

			public static function emergency($message,$value=NULL){
			}

			public static function info($message,$value=NULL){
			}

			public static function success($message,$value=NULL){
			}

		}

	}

