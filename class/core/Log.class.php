<?php
	/**
	*This class is part of Apollo PHP Framework.
	*
	*Namespace	:	apf\core
	*Class		:	Log
	*Description:	A class for logging
	*
	*Author		:	Federico Stange <jpfstange@gmail.com>
	*License		:	3 clause BSD
	*
	*Copyright (c) 2015, Federico Stange
	*
	*All rights reserved.
	*
	*Redistribution and use in source and binary forms, with or without modification, 
	*are permitted provided that the following conditions are met:
	*
	*1. Redistributions of source code must retain the above copyright notice, 
	*this list of conditions and the following disclaimer.
	*
	*2. Redistributions in binary form must reproduce the above copyright notice, 
	*this list of conditions and the following disclaimer in the documentation and/or other 
	*materials provided with the distribution.
	*
	*3. Neither the name of the copyright holder nor the names of its contributors may be used to 
	*endorse or promote products derived from this software without specific prior written permission.
	*
	*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS 
	*OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY 
	*AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER 
	*OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
	*CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	*LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY 
	*OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
	*ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
	*OF SUCH DAMAGE.
	*
	*/

	namespace apf\core {

		use apf\type\base\IntNum					as	IntType;
		use apf\type\util\common\Variable		as	VarUtil;
		use apf\type\validate\common\Variable	as	ValidateVar;

		use apf\type\custom\Parameter				as	ParameterType;
		use apf\type\parser\Parameter				as	ParameterParser;

		class Log implements \apf\iface\Log{

			/**
			 * @var $colors Array Different colors for console output
			 */

			private $colors = Array(
											"black"		=>"\33[0;30m",
											"blue"		=>"\33[0;34m",
											"lblue"		=>"\33[1;34m",
											"green"		=>"\33[0;32m",
											"lgreen"		=>"\33[1;32m",
											"cyan"		=>"\33[0;36m",
											"lcyan"		=>"\33[1;36m",
											"red"			=>"\33[0;31m",
											"lred"		=>"\33[0;31m",
											"purple"		=>"\33[0;35m",
											"lpurple"	=>"\33[1;35m",
											"brown"		=>"\33[0;33m",
											"gray"		=>"\33[1;30m",
											"lgray"		=>"\33[0;37m",
											"yellow"		=>"\33[1;33m",
											"white"		=>"\33[1;37m"
			);


			/**
			 * @var $uselogDate
			 * @see Log::useLogDate($filename)
			 */
		
			private $useLogDate = FALSE;
	
			/**
			 * @var $file String name of log file
			 * @see Log::setFilename($filename)
			 */
	
			private  $file	=	NULL;
	
			/**
			*
			* @var $echo print to stdout or not
			* @see self::setEcho()
			*
			*/
	
			private $echo =	TRUE;
	
			/**
			*
			* @var $usePrefix 
			* @see self::setX11Info()
			*
			*/
	
			private $usePrefix = TRUE;
	
			/**
			*
			* @var $write wether to write to a file or not
			* @see self::setWrite()
			*
			*/
	
			private $write = NULL;
	
	
			/**
			* @var $prepend Adds a static string to every message *before* the message
			* @see Log::setPrepend()
			*/
	
			private $prepend = NULL;
	
			/**
			* @var $append Adds a static string to every message *after* the message
			* @see Log::setAppend()
			*/
	
			private $append = NULL;

			/**
			* @var $lineCharacter it's the character that outputs at the end of a message, by default it's \n
			* @see Log::setCarriageReturnChar
			*/

			private	$lineCharacter	=	"\n";	

			/**
			*@var integer $logLevel
			*
			*This is a variable useful for filtering log messages through log levels
			*If the message to be logged through self::log does not match the given level
			*it will be discarded.
			*
			*The main functionality you can use this for is for having different verbosity levels
			*in your application.
			*/

			private	$logLevel	=	NULL;

			public function __construct($logFile=NULL,$level=NULL){

				if(!is_null($logFile)){

					$this->setFile($logFile);

				}

				if(!is_null($level)){

					$this->setLogLevel($level);

				}

			}

			public function setLogLevel($level){

				$this->logLevel	=	IntType::cast($level)->valueOf();
				return $this;

			}

			public function getLogLevel(){

				return $this->logLevel;

			}

			public function setFile($file){

				$this->file = new File($file);

			}

			public function getFile(){

				return $this->file;

			}
	
			/**
			*Specifies if date should be prepended in the log file
			*@param boolean $boolean TRUE prepend date
			*@param boolean $boolean FALSE do NOT prepend date
			*/
			public function useLogDate($boolean=TRUE){

					$this->useLogDate = $boolean;

			}

			public function setNoLf(){

				$this->lineCharacter	=	'';

			}

			public function setLf(){

				$this->lineCharacter	=	"\n";

			}

			public function usePrefix(){

				$this->usePrefix	=	TRUE;

			}

			public function setNoPrefix(){

				$this->usePrefix	=	'';

			}

			public function line($length=80,$char='-',$color="white"){

				$prepend	=	$this->getPrepend();
				$this->setPrepend('');
				$this->log(sprintf("%s\n",str_repeat($char,$length)),0,$color);
				$this->setPrepend($prepend);

			}

			public function log(){

				$args	=	func_get_args();
				$argc	=	sizeof($args)-1;

				if($argc<0){

					throw new \InvalidArgumentException("Must give at least one argument (the message)");

				}

				$msg			=	$args[0];

				//Check if any parameters where given
				//Parameters are meant to be the LAST argument
				//passed to this method.

				$parameters	=	ParameterParser::parse($argc&&!is_scalar($args[$argc])	?	$args[$argc]	:	NULL);

				$msg			=	VarUtil::printVar($msg,$parameters);

				if(!is_null($this->logLevel)){

					$level		=	$parameters->find('level',NULL)->valueOf();

					if(is_null($level)){

						return;

					}

					$level	=	IntType::cast($level)->valueOf();

					if($this->logLevel!==$level){

						return;

					}

				}

				//Remove first argument which is the message to be logged
				unset($args[0]);

				//Remove the last argument, it's supposed to be $parameters
				unset($args[$argc]);

				//Check if any arguments are left 
				if($argc>=1){

					//If there are any, print as a string each one of them
					foreach($args as &$arg){

						$arg	=	VarUtil::printVar($arg,$parameters);

					}

				}

				$copy			=	$msg;
				$msg			=	@vsprintf($msg,$args);

				if(!$msg){

					$copy	=	preg_replace('/%s/u','<arg>',$copy);
					$msg	=	"Insufficient arguments provided for templating string: $copy";
					throw new \InvalidArgumentException($msg);

				}

				unset($copy);

				$type			=	$parameters->find('type',0)->toInt()->valueOf();
				$color		=	$parameters->find('color','white')->toString()->valueOf();

				$msg			=	VarUtil::printVar($msg,$parameters);

				if(is_null($msg)){

					throw(new \Exception("Message to be logged cant be empty"));

				}

				$date	=	$parameters->find('useDate',$this->useLogDate)->toBoolean()->valueOf();
				$date =	$date	? date("[d-M-Y / H:i:s]") : NULL;
			
				$code = NULL;
	
				$prefix	=	$parameters->find('usePrefix',$this->usePrefix)->toBoolean()->valueOf();
				$prefix 	=	$prefix	?	sprintf('%s ',$this->infoType($type)) : '';

				$origMsg	=	$msg;	
				$msg		=	sprintf('%s%s%s%s%s',$this->prepend,$prefix,$date,$msg,$this->append);
	
				if($this->echo){

					$outputMsg	=	$color	?	$this->colorString($msg,$color)	:	$msg;

					echo sprintf('%s%s',$outputMsg,$this->lineCharacter);
	
				}
	
				if(!is_null($this->file)){

					return $this->file->write(sprintf('%s%s',$msg,$this->lineCharacter));
	
				}
			
			}

			private function colorString($string,$color){

				if(!in_array($color,array_keys($this->colors))){

					throw new \Exception("No such color: $color");

				}

				return sprintf('%s%s%s',$this->colors[$color],$string,"\033[37m");

			}

			public function reset(){

				echo $this->colors["lgray"]."\r";

			}
	
	
			/**
			*Returns an X11 debug like tag according to the given number
			*/
	
			private function infoType($type=NULL) {
	
				switch($type) {
					case 0:
						return '[II]';
					case 1:
						return '[SS]';
					case 2:
						return '[DD]';
					case 3:
						return '[WW]';
					case 4:
						return '[EE]';
					case 5:
						return '[!!]';
					default:
						return "[II]";
				}
	
			}

			private function logType($text,$type,$color,$args){

				$argc			=	sizeof($args)-1;

				//THE LAST ARGUMENT is supposed to be the parameters
				$lastArg		=	$args[$argc];

				unset($args[0]);

				$parameters	=	ParameterParser::parse(['type'=>$type,'color'=>$color]);

				//If the last argument is NOT scalar it's supposed to be parameters
				if(is_array($lastArg)){

					foreach($lastArg as $key=>$value){
						
						$parameters->add(ParameterType::cast($key,$value));

					}


				}

				$args[]	=	$parameters;
				$args[0]	=	$text;

				ksort($args);

				call_user_func_array(Array($this,'log'),$args);

			}

			public function info($text=NULL){

				return $this->logType($text,0,'lcyan',func_get_args());

			}

			public function success($text=NULL){

				return $this->logType($text,1,'lgreen',func_get_args());

			}

			public function debug($text=NULL){

				return $this->logType($text,2,'lpurple',func_get_args());

			}

			public function warning($text=NULL){

				return $this->logType($text,3,'yellow',func_get_args());

			}

			public function error($text=NULL){

				return $this->logType($text,4,'lred',func_get_args());

			}


			public function emergency($text=NULL){

				return $this->logType($text,5,'red',func_get_args());

			}

			/**
			* @method endLog() closes pointer to created file
			*/

			public function endLog() {

				if(!is_null($this->file)){

					$this->file->close();

				}
	
			}
	
			/**
			*@method setEcho() 
			*@param $echo bool TRUE output to stdout
			*@param $echo bool FALSE Do NOT output to stdout
			*/
	
			public function setEcho($echo=TRUE) {
	
				$this->echo = $echo;
	
			}
	
			/**
			 *@method setPrepend() Prepends a string to every log message
			 *@param String The string to be prepend
			 */
	
			public function setPrepend($prepend=NULL) {
	
				$this->prepend = $prepend;
	
			}
	
			/**
			*@method setAppend() Adds 
			*@param string El string a posponer en el mensaje log
			*
			*/
	
			public function setAppend($append=NULL) {
	
				$this->append = $append;
	
			}
	
			public function getAppend(){
	
				return $this->append;
		
			}
	
			public function getPrepend(){
	
				return $this->prepend;
	
			}
	
	
			/**
			* @method setColors() Color output (Console only)
			* @param bool $bool TRUE ACTIVADO FALSE DESACTIVADO
			*/
	
			public function setColors($bool=TRUE) {

				$this->colors=$bool;

			}

			public function getEcho(){

				return $this->echo;

			}

			public function __destruct(){

				$this->endLog();

			}
	
		}

	}
