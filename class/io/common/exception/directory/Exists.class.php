<?php

	namespace apf\io\common\exception\directory{

		class Exists extends \Exception{

			public function __construct($message,$code=0,\Exception $previous=NULL){
				
				$msg	=	sprintf('Directory "%s" already exists',$message);
				parent::__construct($msg,$code,$previous);

			}

		}

	}
