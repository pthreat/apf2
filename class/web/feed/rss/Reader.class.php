<?php

		namespace apf\feed\rss{

			class Consumer extends \apf\web\Feed{

				public function __construct(Array $arguments){

					parent::__construct($arguments);

				}

				public function fetch(){

					if(is_null($this->getUri())){

						throw(new \Exception("Can't get RSS content, no URI specified"));

					}

					$file	=	tempnam(sys_get_temp_dir(),__CLASS__);

					$adapter	=	$this->getAdapter();

					//If adapter is of type eCurl

					if(is_a($this->getAdapter(),"\\apf\\http\\adapter\\Ecurl")){

						$adapter->setCurlOption("FILE",$file);
						$adapter->setCurlOption("RETURNTRANSFER",FALSE);

						$adapter->connect();

						$file		=	new \apf\core\File($file);
						$content	=	$file->read();

					}else{

						$content	=	$adapter->connect();

						file_put_contents($content,$file);

					}

					$msg	=	"The given URI ".$this->getUri()." has return no content!";
					\apf\Validator::emptyString($content,$msg);

					return new \apf\parser\feed\Rss($file);

            }

        }
        
    }

?>
