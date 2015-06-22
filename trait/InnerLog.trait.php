<?php

	namespace apf\traits{

		use apf\iface\Log	as	LogInterface;

		trait InnerLog{

			private	$log	=	NULL;

			public function setLog(LogInterface $log){

				$this->log	=	$log;

				return $this;

			}

			protected function logDebug($message=NULL){

				if(is_null($this->log)){

					return;

				}

				$this->log->debug($message);

			}

			protected function logInfo($message=NULL){

				if(is_null($this->log)){

					return;

				}

				$this->log->info($message);

			}

			protected function logError($message=NULL){

				if(is_null($this->log)){

					return;

				}

				$this->log->error($message);

			}

			protected function logEmergency($message=NULL){

				if(is_null($this->log)){

					return;

				}

				$this->log->emergency($message);

			}

			protected function logWarning($message=NULL){

				if(is_null($this->log)){

					return;

				}

				$this->log->warning($message);

			}

		}

	}
