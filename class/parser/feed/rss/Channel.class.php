<?php

	namespace apf\parser\feed\rss{

		class Channel implements \Iterator{

			private	$reader		=	NULL;
			private	$validXML	=	FALSE;

			public function __construct(\XMLReader $xml){

				$this->reader	=	$xml;
				$this->parse();

			}

			public function rewind(){
			}

			public function current(){
			}

			public function key(){
			}

			public function next(){
			}

			public function valid(){
			}

			public function parse(){

				
				$xml	=	$this->reader->read();

				var_dump($xml);
				die();

				while($xml=$this->reader->read()){

					var_dump($xml);
					var_dump($this->reader->isValid());
					die();

					if($this->reader->name=="#text"){

						continue;

					}

					echo $this->reader->name."\n";

				}


			}

			private function validateXML(){

				if($this->validXML){

					return;

				}

				$foundRss		=	FALSE;
				$foundChannel	=	FALSE;

				while($xml=$this->reader->read()){

					if($this->reader->name=="#text"){

						continue;

					}


					if($this->reader->name=="rss"){

						$foundRss	=	TRUE;
						continue;

					}

					if($foundRss&&$this->reader->name=="channel"){
						$foundChannel=TRUE;
						break;
					}

				}

				if(!$foundRss){

					throw(new \Exception("rss tag was not found in the provided XML"));

				}

				if(!$foundChannel){


					throw(new \Exception("channel tag not found in the provided XML"));

				}

				$this->validXML	=	TRUE;

			}

			public function __get($var){
			}

		}

	}

?>
