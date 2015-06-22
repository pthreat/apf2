<?php

	namespace apf\parser\feed\rss{

		class Reader extends \apf\parser\Feed {

			public function __construct($xml=NULL){

				parent::__construct($xml);

			}

			public function getXSD(){

				return new \apf\core\File(__DIR__.DIRECTORY_SEPARATOR."rss20.xsd");

			}

			public function getChannel(){

				new \apf\parser\feed\rss\Channel($this->getXMLReader());

			}

			public function parse($content){
			}

		}

	}

?>
