<?php

	namespace apf\parser{

		abstract class Feed implements ParserInterface{

			private	$xml		=	Array(
												"is_file"	=>NULL,
												"data"		=>NULL,
												"set_calls"	=>0
			);

			private	$reader	=	NULL;
			private	$adapter	=	NULL;

			/**
			*The trick here is that $xml could be a path to a file or a string containing XML
			*or even a URL
			*/

			public function __construct($source=NULL,\apf\Adapter $adapter=NULL){

				if(!is_null($source)){

					$this->setXML($source);

				}

				if(!is_null($adapter)){

					$this->setAdapter($adapter);

				}

			}

			public function setAdapter(\apf\Adapter $adapter=NULL){

				$this->adapter	=	clone($adapter);

			}

			public function getAdapter(){

				return $this->adapter;

			}

			private function fetch($uri){

				$file		=	new \apf\core\File(tempnam(sys_get_temp_dir(),__CLASS__));
				$adapter	=	$this->getAdapter();

				if(is_null($adapter)){

					$adapter	=	new \apf\http\adapter\Ecurl();
					$adapter->setCurlOption("HEADER",FALSE);

				}

				//Some feeds can be pretty massive, thats the reason why we save the feed to a local file
				//We could also implement some type of cache at later stages.

				$adapter->setHttpMethod("GET");
				$adapter->setUri(new \apf\parser\Uri($uri));
				$adapter->save($file);
				$content	=	$file->read(1024);

				$msg	=	"The given URI ".$adapter->getUri()." has return no content!";
				\apf\Validator::emptyString($content,$msg);

				return $file;

			}


			public function setXml(&$xml=NULL){

				$msg	=	"Given XML has to be a path to a file, an XML string or a valid URL empty string given";
				\apf\Validator::emptyString($xml,$msg);

				$this->xml["set_calls"]++;

				$scheme			=	parse_url($xml,PHP_URL_SCHEME);
				$validSchemes	=	Array("http","ftp","file","https");

				if(is_file($xml)||$scheme=="file"){

					$this->xml["is_file"]	=	TRUE;
					$this->xml["data"]		=	new \apf\core\File($xml);
					return;

				}

				if(in_array($scheme,$validSchemes)){

					//The private method "fetch" saves to a temporary file in the filesystem,
					//this is in case the programmer has given a URI as input instead of a file.

					$this->xml["data"]		=	$this->fetch($uri=$xml);
					$this->xml["is_file"]	=	TRUE;

					return;

				}

				//Else ... $xml is a string

				$this->xml["is_file"]	=	FALSE;
				$this->xml["data"]		=	$xml;

			}

			abstract public function getXSD();

			public function getXml(){

				return $this->xml;

			}

			public function getXmlReader(){

				if($this->xml["set_calls"]==0){

					throw(new \Exception("XML String or File have not been set, please call setXml first or pass the argument on class instantiation"));

				}

				if(!is_null($this->reader)&&$this->xml["set_calls"]==1){

					return $this->reader;

				}

				//reset the calls counter
				$this->xml["set_calls"]	=	1;

				if(!file_exists($this->getXSD())){

					throw(new \Exception("XSD file ".$this->getXSD()." doesn't exists"));

				}


				$this->reader	=	new \XMLReader();

				if($this->xml["is_file"]){

					$this->reader->open($this->xml["data"]);

					$this->reader->setSchema($this->getXSD());

					return $this->reader;

				}

				//Else if it's an XML string ...

				$this->reader->xml($this->xml["data"]);

				$this->reader->setSchema($this->getXSD());

				return $this->reader;

			}

			public function __destruct(){

				if(!is_null($this->reader)){

					$this->reader->close();

				}

			}

		}

	}

?>
