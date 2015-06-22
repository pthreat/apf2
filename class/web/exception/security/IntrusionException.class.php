<?php

	namespace apf\web\exception\security{

		use apf\web\core\Server;

		class IntrusionException extends \Exception{

			private	$ip	=	NULL;

			public function __construct($message, $code = 0, Exception $previous = null) {

				$this->ip	=	Server::getRemoteAddr();

				$message	=	sprintf('IP: %s | %s',$this->ip,$message);

				parent::__construct($message,$code,$previous);

			}

			public function getIp(){

				return $this->ip;

			}

			public function __toString(){

				return sprintf('CODE: %s | MESSAGE: %s',$this->code,$this->message);

			}

		}

	}

